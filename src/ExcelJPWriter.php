<?php

declare(strict_types=1);

namespace Ad5jp\SchemaWriter;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelJPWriter implements Writer
{
    public string $template;
    public string $output_dir;
    public string $output_filename;

    public function __construct()
    {
        $default_template = __DIR__ . '/../resources/table_definitions.xlsx';
        $default_output_dir = __DIR__ . '/../output';
        $default_output_filename = Config::getValue('schema', 'table_definitions') . date('_Ymd');

        $this->template = Config::getValue('template', $default_template);
        $this->output_dir = Config::getValue('output_dir', $default_output_dir);
        $this->output_filename = Config::getValue('output_filename', $default_output_filename);
    }

    public function prepare(): void
    {
        // do nothing
    }

    /**
     * @inheritdoc
     */
    public function write(array $tables): string
    {
        $spreadsheet = IOFactory::load($this->template);

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
        $titles = ['テーブル一覧' => 1, '雛形' => 1];
        foreach ($tables as $table) {
            $column_start_row = 7;
            $index_start_row = 13;

            // 雛形を複製
            $templateSheet = $spreadsheet->getSheetByName('雛形');
            $sheet = clone $templateSheet;

            // シート名
            $title = $table->comment ?: $table->name;
            // 同じシート名が既にあれば _$i をつける
            $titles[$title] = $i = ($titles[$title] ?? 0) + 1;
            if ($i > 1) {
                $title = "{$title}_{$i}";
            }

            $sheet->setTitle($title);

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

        $output_path = rtrim($this->output_dir, '/') . '/' . $this->output_filename . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($output_path);

        return "Saved as: " . $output_path;
    }
}
