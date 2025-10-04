<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelWriter implements Writer
{
    /**
     * @inheritdoc
     */
    public function write(array $tables, ?string $schema = null): string
    {
        $spreadsheet = IOFactory::load(__DIR__ . '/../resources/table_definitions.xlsx');

        // --------------------------------
        // テーブル一覧シート
        // --------------------------------
        $sheet = $spreadsheet->getSheetByName('テーブル一覧');

        // テーブルの分だけ行を追加する（雛形は3行のみセットされてる）
        $sheet->insertNewRowBefore(3, count($tables) - 3);

        foreach ($tables as $index => $table) {
            $row = $index + 2;
            $sheet->setCellValue("A{$row}", $table->name);
            $sheet->setCellValue("B{$row}", $table->comment);
        }

        // --------------------------------
        // テーブル別シート
        // --------------------------------
        foreach ($tables as $table) {
            $templateSheet = $spreadsheet->getSheetByName('雛形');

            $column_start_row = 7;
            $index_start_row = 13;

            $sheet = clone $templateSheet;
            $sheet->setTitle($table->comment ?: $table->name);

            // ヘッダ部分
            $sheet->setCellValue("B1", $table->name);
            $sheet->setCellValue("B2", $table->comment);

            // カラムの分だけ行を追加する（雛形は3行のみセットされてる）
            if (count($table->columns) > 3) {
                $sheet->insertNewRowBefore($column_start_row + 2, count($table->columns) - 3);
                $index_start_row += count($table->columns) - 3;
            }

            foreach ($table->columns as $i => $column) {
                $logical_name = $column->comment;
                $comment = "";
                if (preg_match('/(.+)\:\[(.+)\]/', $column->comment, $matches)) {
                    $logical_name = $matches[1];
                    $comment = $matches[2];
                }

                /** @var Column $column */
                $row = $i + $column_start_row;
                $sheet->setCellValue("A{$row}", $column->name);
                $sheet->setCellValue("B{$row}", $logical_name);
                $sheet->setCellValue("C{$row}", $column->type);
                $sheet->setCellValue("D{$row}", $column->nullable ? '' : '◯');
                $sheet->setCellValue("E{$row}", $column->default);
                $sheet->setCellValue("F{$row}", $column->extra);
                $sheet->setCellValue("G{$row}", $comment);
            }

            // インデックスの分だけ行を追加する（雛形は3行のみセットされてる）
            if (count($table->indexes) > 3) {
                $sheet->insertNewRowBefore($index_start_row + 2, count($table->indexes) - 3);
            }

            foreach ($table->indexes as $i => $index) {
                /** @var Index $index */
                $row = $i + $index_start_row;
                $sheet->setCellValue("A{$row}", $index->name);
                $sheet->setCellValue("B{$row}", join(', ', $index->columns));
                $sheet->setCellValue("G{$row}", $index->unique ? '◯' : '');
            }

            $spreadsheet->addSheet($sheet);
        }

        $filename = ($schema ?: 'table_definitions') . date('_Ymd') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save(__DIR__ . "/../{$filename}");

        return $filename;
    }
}
