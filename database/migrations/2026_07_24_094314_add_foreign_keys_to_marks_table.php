<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = Schema::getColumnListing('marks');
        $foreignKeyColumns = collect(Schema::getForeignKeys('marks'))
            ->flatMap(fn (array $foreignKey) => $foreignKey['columns'])
            ->all();

        if (in_array('student_id', $columns, true)) {
            DB::table('marks')
                ->whereNotNull('student_id')
                ->whereNotExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('students')
                        ->whereColumn('students.id', 'marks.student_id');
                })
                ->delete();
        }

        if (in_array('subject_id', $columns, true)) {
            DB::table('marks')
                ->whereNotNull('subject_id')
                ->whereNotExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('subjects')
                        ->whereColumn('subjects.id', 'marks.subject_id');
                })
                ->delete();
        }

        if (!in_array('student_id', $columns, true) || !in_array('subject_id', $columns, true)) {
            Schema::table('marks', function (Blueprint $table) use ($columns): void {
                if (!in_array('student_id', $columns, true)) {
                    $table->unsignedBigInteger('student_id')->after('id');
                }

                if (!in_array('subject_id', $columns, true)) {
                    $table->unsignedBigInteger('subject_id')->after(in_array('student_id', $columns, true) ? 'student_id' : 'id');
                }
            });
        }

        $foreignKeysToAdd = [];

        if (!in_array('student_id', $foreignKeyColumns, true)) {
            $foreignKeysToAdd[] = ['column' => 'student_id', 'table' => 'students'];
        }

        if (!in_array('subject_id', $foreignKeyColumns, true)) {
            $foreignKeysToAdd[] = ['column' => 'subject_id', 'table' => 'subjects'];
        }

        if ($foreignKeysToAdd !== []) {
            Schema::table('marks', function (Blueprint $table) use ($foreignKeysToAdd): void {
                foreach ($foreignKeysToAdd as $foreignKey) {
                    $table->foreign($foreignKey['column'])
                        ->references('id')
                        ->on($foreignKey['table'])
                        ->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        // The columns may predate this migration, so rollback must not remove them.
    }
};
