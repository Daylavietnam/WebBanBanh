<?php
/**
 * Validator Helper Class
 * Validate dữ liệu form
 */

class Validator {
    private $errors = [];
    private $data = [];

    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Validate required field
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? ucfirst($field) . ' là bắt buộc';
        }
        return $this;
    }

    /**
     * Validate email
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Email không hợp lệ';
        }
        return $this;
    }

    /**
     * Validate min length
     */
    public function min($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?? ucfirst($field) . " phải có ít nhất {$length} ký tự";
        }
        return $this;
    }

    /**
     * Validate max length
     */
    public function max($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?? ucfirst($field) . " không được vượt quá {$length} ký tự";
        }
        return $this;
    }

    /**
     * Validate phone number
     */
    public function phone($field, $message = null) {
        if (isset($this->data[$field])) {
            $pattern = '/^(0|\+84)[0-9]{9}$/';
            if (!preg_match($pattern, $this->data[$field])) {
                $this->errors[$field] = $message ?? 'Số điện thoại không hợp lệ';
            }
        }
        return $this;
    }

    /**
     * Validate numeric
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' phải là số';
        }
        return $this;
    }

    /**
     * Validate match (for password confirmation)
     */
    public function match($field, $matchField, $message = null) {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) && 
            $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = $message ?? 'Mật khẩu xác nhận không khớp';
        }
        return $this;
    }

    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails() {
        return !$this->passes();
    }

    /**
     * Get all errors
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Get first error
     */
    public function firstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Get error for specific field
     */
    public function error($field) {
        return $this->errors[$field] ?? null;
    }
}
?>