<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSchemaVisualization extends Command
{
    protected $signature = 'schema:visualize {--output=schema.svg}';
    protected $description = 'Generate a visual representation of the database schema';

    private $tables = [];
    private $relationships = [];
    private $tablePositions = [];

    public function handle()
    {
        $this->getTables();
        $this->getRelationships();
        $svg = $this->generateSVG();

        $outputPath = $this->option('output');
        file_put_contents($outputPath, $svg);

        $this->info("Schema visualization saved to $outputPath");
    }

    private function getTables()
    {
        $schema = config('database.connections.' . config('database.default') . '.schema', 'public');

        $results = DB::select("
            SELECT
                table_name,
                column_name,
                data_type,
                character_maximum_length,
                is_nullable,
                column_default
            FROM information_schema.columns
            WHERE table_schema = ?
            ORDER BY table_name, ordinal_position", [$schema]);

        foreach ($results as $result) {
            if (!isset($this->tables[$result->table_name])) {
                $this->tables[$result->table_name] = ['columns' => []];
            }
            $this->tables[$result->table_name]['columns'][] = [
                'name' => $result->column_name,
                'type' => $this->formatColumnType($result),
                'nullable' => $result->is_nullable === 'YES',
                'default' => $result->column_default,
            ];
        }
    }

    private function formatColumnType($column)
    {
        $type = $column->data_type;
        if ($column->character_maximum_length) {
            $type .= "({$column->character_maximum_length})";
        }
        return $type;
    }

    private function getRelationships()
    {
        $schema = config('database.connections.' . config('database.default') . '.schema', 'public');

        $results = DB::select("
            SELECT
                tc.table_name AS from_table,
                kcu.column_name AS from_column,
                ccu.table_name AS to_table,
                ccu.column_name AS to_column
            FROM information_schema.table_constraints AS tc
            JOIN information_schema.key_column_usage AS kcu
                ON tc.constraint_name = kcu.constraint_name
                AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage AS ccu
                ON ccu.constraint_name = tc.constraint_name
                AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY'
            AND tc.table_schema = ?", [$schema]);

        foreach ($results as $result) {
            $this->relationships[] = [
                'from' => $result->from_table,
                'to' => $result->to_table,
                'fromColumn' => $result->from_column,
                'toColumn' => $result->to_column,
            ];
        }
    }

    private function generateSVG()
    {
        $canvasWidth = 3000;
        $canvasHeight = 2000;
        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='$canvasWidth' height='$canvasHeight'>";

        $x = 50;
        $y = 50;
        $maxHeight = 0;

        foreach ($this->tables as $tableName => $tableInfo) {
            $tableHeight = $this->drawTable($svg, $tableName, $tableInfo, $x, $y);
            $this->tablePositions[$tableName] = ['x' => $x, 'y' => $y, 'height' => $tableHeight];

            $maxHeight = max($maxHeight, $tableHeight);

            $x += 300;
            if ($x > $canvasWidth - 300) {
                $x = 50;
                $y += $maxHeight + 50;
                $maxHeight = 0;
            }
        }

        foreach ($this->relationships as $rel) {
            $this->drawRelationship($svg, $rel);
        }

        $svg .= '</svg>';
        return $svg;
    }

    private function drawTable(&$svg, $tableName, $tableInfo, $x, $y)
    {
        $width = 250;
        $headerHeight = 30;
        $rowHeight = 20;
        $height = $headerHeight + count($tableInfo['columns']) * $rowHeight;

        // Table header
        $svg .= "<rect x='$x' y='$y' width='$width' height='$headerHeight' fill='#4a69bd' />";
        $svg .= "<text x='" . ($x + 5) . "' y='" . ($y + 20) . "' fill='white' font-weight='bold'>$tableName</text>";

        // Columns
        foreach ($tableInfo['columns'] as $index => $column) {
            $yPos = $y + $headerHeight + ($index * $rowHeight);
            $fillColor = ($index % 2 == 0) ? '#f1f2f6' : '#dfe4ea';
            $svg .= "<rect x='$x' y='$yPos' width='$width' height='$rowHeight' fill='$fillColor' />";
            $svg .= "<text x='" . ($x + 5) . "' y='" . ($yPos + 15) . "' font-size='12'>";
            $svg .= $column['name'] . ": " . $column['type'];
            if ($column['nullable']) {
                $svg .= " (nullable)";
            }
            $svg .= "</text>";
        }

        return $height;
    }

    private function drawRelationship(&$svg, $rel)
    {
        $fromTablePos = $this->tablePositions[$rel['from']];
        $toTablePos = $this->tablePositions[$rel['to']];

        // Determine start and end points of the line
        $startX = $fromTablePos['x'] + 250;
        $startY = $fromTablePos['y'] + 15;
        $endX = $toTablePos['x'];
        $endY = $toTablePos['y'] + 15;

        // Draw line between tables
        $svg .= "<line x1='$startX' y1='$startY' x2='$endX' y2='$endY' stroke='#2c3e50' stroke-width='2' />";
        $svg .= "<text x='" . (($startX + $endX) / 2) . "' y='" . (($startY + $endY) / 2 - 5) . "' font-size='10' fill='#2c3e50'>{$rel['from']}.{$rel['fromColumn']} → {$rel['to']}.{$rel['toColumn']}</text>";
    }
}
