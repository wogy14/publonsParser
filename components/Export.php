<?php

namespace components;

use PDO;

class Export {
	const EXPORT_PATH = 'exported/';

	public static function getExportData() {
		$conn = DB::getConnection();
		$authorsSql = 'SELECT * FROM `author`';
		$authors = [];

		foreach ($conn->query($authorsSql, PDO::FETCH_NAMED) as $row) {
			$authors[] = [
				'id'                 => $row['id'],
				'pid'                => $row['pid'],
				'publications_count' => $row['publications_count'],
				'rid'                => htmlspecialchars_decode($row['rid']),
				'full_name'          => htmlspecialchars_decode($row['full_name']),
				'h_index'            => $row['h_index'],
				'times_cited'        => $row['times_cited'],
				'orcid'              => htmlspecialchars_decode($row['orcid']),
				'photo'              => htmlspecialchars_decode($row['photo']),
				'citations_per_year' => json_decode($row['citations_per_year'] ?? 'null'),
				'publons_updated_at' => htmlspecialchars_decode($row['publons_updated_at'])
			];
		}

		$publicationsSql = 'SELECT * FROM `publication`';
		$publications = [];

		foreach ($conn->query($publicationsSql, PDO::FETCH_NAMED) as $row) {
			$publications[] = [
				'id'               => $row['id'],
				'title'            => htmlspecialchars_decode($row['title']),
				'author_id'        => $row['author_id'],
				'journal_title'    => htmlspecialchars_decode($row['journal_title']),
				'published_at'     => htmlspecialchars_decode($row['published_at']),
				'times_cited'      => $row['times_cited'],
				'identifier_name'  => htmlspecialchars_decode($row['identifier_name']),
				'identifier_value' => htmlspecialchars_decode($row['identifier_value'])
			];
		}

		return [
			'authors'      => $authors,
			'publications' => $publications
		];
	}

	public static function json() {
		$filePath = self::EXPORT_PATH . 'publons_' . time() . '.json';
		$data = self::getExportData();
		file_put_contents($filePath, json_encode($data));

		echo "Exported to $filePath\n";
	}

	private static function saveCsv($filePath, $data) {
		$fp = fopen($filePath, 'w');

		fputcsv($fp, array_keys($data[0]));

		foreach ($data as $fields) {
			if (!empty($fields['citations_per_year'])) {
				$fields['citations_per_year'] = json_encode($fields['citations_per_year']);
			}
			fputcsv($fp, $fields);
		}

		fclose($fp);
	}

	public static function csv() {
		$data = self::getExportData();
		$pathToFolder = self::EXPORT_PATH . 'publons_' . time() . '_csv/';
		mkdir($pathToFolder, 0755, true);

		self::saveCsv($pathToFolder . 'author.csv', $data['authors']);
		self::saveCsv($pathToFolder . 'publication.csv', $data['publications']);

		echo "Exported to $pathToFolder\n";
	}
}
