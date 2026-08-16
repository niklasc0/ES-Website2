<?php
/**
 * Minimaler XLSX-Writer/-Reader (ein Arbeitsblatt, ohne Bibliotheken) für den
 * Übersetzungs-Roundtrip: Export der aktuellen DE/EN-Texte, Re-Import der vom
 * Kunden befüllten Datei.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ES_Lang_Xlsx {

	/** Schreibt Zeilen (Array von Arrays) als XLSX in eine Datei. */
	public static function write( $file, $rows ) {
		$zip = new ZipArchive();
		if ( true !== $zip->open( $file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) { return false; }
		$zip->addFromString( '[Content_Types].xml',
			'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
			. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
			. '<Default Extension="xml" ContentType="application/xml"/>'
			. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
			. '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
			. '</Types>' );
		$zip->addFromString( '_rels/.rels',
			'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
			. '</Relationships>' );
		$zip->addFromString( 'xl/workbook.xml',
			'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
			. '<sheets><sheet name="Uebersetzungen" sheetId="1" r:id="rId1"/></sheets></workbook>' );
		$zip->addFromString( 'xl/_rels/workbook.xml.rels',
			'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
			. '</Relationships>' );
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
			. '<cols><col min="1" max="1" width="20" customWidth="1"/><col min="2" max="2" width="22" customWidth="1"/>'
			. '<col min="3" max="4" width="60" customWidth="1"/><col min="5" max="5" width="30" customWidth="1"/></cols><sheetData>';
		$r = 0;
		foreach ( $rows as $row ) {
			$r++;
			$xml .= '<row r="' . $r . '">';
			$c = 0;
			foreach ( $row as $val ) {
				$c++;
				$cell = self::col_letter( $c ) . $r;
				$xml .= '<c r="' . $cell . '" t="inlineStr"><is><t xml:space="preserve">'
					. htmlspecialchars( (string) $val, ENT_XML1 | ENT_COMPAT, 'UTF-8' )
					. '</t></is></c>';
			}
			$xml .= '</row>';
		}
		$xml .= '</sheetData></worksheet>';
		$zip->addFromString( 'xl/worksheets/sheet1.xml', $xml );
		$zip->close();
		return true;
	}

	protected static function col_letter( $n ) {
		$s = '';
		while ( $n > 0 ) { $m = ( $n - 1 ) % 26; $s = chr( 65 + $m ) . $s; $n = (int) ( ( $n - $m - 1 ) / 26 ); }
		return $s;
	}

	/** Liest das erste Arbeitsblatt einer XLSX als Array von Zeilen-Arrays. */
	public static function read( $file ) {
		$zip = new ZipArchive();
		if ( true !== $zip->open( $file ) ) { return new WP_Error( 'xlsx', 'Datei konnte nicht geöffnet werden.' ); }
		// Shared strings (Excel legt Texte meist hier ab)
		$shared = array();
		$ss = $zip->getFromName( 'xl/sharedStrings.xml' );
		if ( false !== $ss ) {
			$sx = simplexml_load_string( $ss );
			if ( $sx ) {
				foreach ( $sx->si as $si ) {
					if ( isset( $si->t ) ) { $shared[] = (string) $si->t; }
					else { $t = ''; foreach ( $si->r as $run ) { $t .= (string) $run->t; } $shared[] = $t; }
				}
			}
		}
		// Erstes Sheet finden (sheet1.xml ist Standard)
		$sheet = $zip->getFromName( 'xl/worksheets/sheet1.xml' );
		if ( false === $sheet ) {
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				$name = $zip->getNameIndex( $i );
				if ( preg_match( '#^xl/worksheets/sheet\d+\.xml$#', $name ) ) { $sheet = $zip->getFromName( $name ); break; }
			}
		}
		$zip->close();
		if ( false === $sheet ) { return new WP_Error( 'xlsx', 'Kein Arbeitsblatt gefunden.' ); }
		$sx = simplexml_load_string( $sheet );
		if ( ! $sx ) { return new WP_Error( 'xlsx', 'Arbeitsblatt nicht lesbar.' ); }
		$rows = array();
		foreach ( $sx->sheetData->row as $row ) {
			$cells = array();
			foreach ( $row->c as $c ) {
				$ref = (string) $c['r'];
				$col = 0;
				foreach ( str_split( preg_replace( '/\d+/', '', $ref ) ) as $ch ) { $col = $col * 26 + ( ord( $ch ) - 64 ); }
				$type = (string) $c['t'];
				if ( 'inlineStr' === $type ) { $val = isset( $c->is->t ) ? (string) $c->is->t : ''; }
				elseif ( 's' === $type ) { $val = isset( $shared[ (int) $c->v ] ) ? $shared[ (int) $c->v ] : ''; }
				else { $val = isset( $c->v ) ? (string) $c->v : ''; }
				$cells[ $col - 1 ] = $val;
			}
			if ( $cells ) { ksort( $cells ); $rows[] = $cells; }
		}
		return $rows;
	}
}
