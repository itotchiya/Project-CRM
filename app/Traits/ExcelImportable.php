<?php

namespace App\Traits;

trait ExcelImportable
{

    private function getColumnValue(string $column)
    {
        return $this->isColumnExists($column) ? $this->row[array_keys($this->columns, $column)[0]] : null;
    }

    private function isColumnExists(string $column)
    {
        return !empty(array_keys($this->columns, $column));
    }

    private function getRowValuesAsString(array $values)
    {
        return implode(', ', $values);
    }

    private function failJob(string $message)
    {
        $this->job->fail($message . $this->getRowValuesAsString($this->row));
    }

    private function failJobWithMessage(string $message)
    {
        $this->job->fail($message);
    }

    private function isEmailValid(string|null $email)
    {
        if (empty($email)) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

}
