<?php

class InputValidator {
    public static function normalizeWhitespace($value) {
        $value = trim((string) $value);
        return preg_replace('/\s+/', ' ', $value);
    }

    public static function normalizeCode($code) {
        $code = strtoupper(trim((string) $code));
        return preg_replace('/\s+/', '', $code);
    }

    public static function validatePersonName($name, $label = 'Name') {
        $name = self::normalizeWhitespace($name);

        if ($name === '') {
            throw new RuntimeException($label . ' is required.');
        }

        if (strlen($name) < 2 || strlen($name) > 120) {
            throw new RuntimeException($label . ' must be between 2 and 120 characters.');
        }

        if (!preg_match('/^[A-Za-z0-9 .\'\-]+$/', $name)) {
            throw new RuntimeException($label . ' contains invalid characters.');
        }

        return $name;
    }

    public static function validateCompanyName($companyName) {
        $companyName = self::normalizeWhitespace($companyName);

        if ($companyName === '') {
            throw new RuntimeException('Company name is required.');
        }

        if (strlen($companyName) < 2 || strlen($companyName) > 255) {
            throw new RuntimeException('Company name must be between 2 and 255 characters.');
        }

        if (!preg_match('/^[A-Za-z0-9 .,&()\'\-\/]+$/', $companyName)) {
            throw new RuntimeException('Company name contains invalid characters.');
        }

        return $companyName;
    }

    public static function validateEmail($email) {
        $email = strtolower(trim((string) $email));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Please provide a valid email address.');
        }

        if (strlen($email) > 255) {
            throw new RuntimeException('Email address is too long.');
        }

        return $email;
    }

    public static function validatePassword($password, $minLength = 8) {
        $password = (string) $password;

        if (strlen($password) < $minLength) {
            throw new RuntimeException('Password must be at least ' . $minLength . ' characters.');
        }

        if (strlen($password) > 128) {
            throw new RuntimeException('Password is too long.');
        }

        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            throw new RuntimeException('Password must contain at least one letter and one number.');
        }

        return $password;
    }

    public static function validateLicenseNumber($licenseNumber) {
        $licenseNumber = self::normalizeCode($licenseNumber);

        if ($licenseNumber === '') {
            throw new RuntimeException('License number is required.');
        }

        if (strlen($licenseNumber) < 5 || strlen($licenseNumber) > 40) {
            throw new RuntimeException('License number must be between 5 and 40 characters.');
        }

        if (!preg_match('/^[A-Z0-9\-\/]+$/', $licenseNumber)) {
            throw new RuntimeException('License number format is invalid.');
        }

        return $licenseNumber;
    }

    public static function validateCountry($country) {
        $country = self::normalizeWhitespace($country);

        if ($country === '') {
            return '';
        }

        if (strlen($country) < 2 || strlen($country) > 120) {
            throw new RuntimeException('Country must be between 2 and 120 characters.');
        }

        if (!preg_match('/^[A-Za-z .\'\-]+$/', $country)) {
            throw new RuntimeException('Country contains invalid characters.');
        }

        return $country;
    }

    public static function validatePhone($phone) {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return '';
        }

        if (strlen($phone) > 30) {
            throw new RuntimeException('Phone number is too long.');
        }

        if (!preg_match('/^\+?[0-9 ()\-]{7,30}$/', $phone)) {
            throw new RuntimeException('Phone number format is invalid.');
        }

        return $phone;
    }

    public static function validateAddress($address) {
        $address = trim((string) $address);

        if ($address === '') {
            return '';
        }

        if (strlen($address) > 500) {
            throw new RuntimeException('Address is too long.');
        }

        return $address;
    }

    public static function validateMedicineName($medicineName) {
        $medicineName = self::normalizeWhitespace($medicineName);

        if ($medicineName === '') {
            throw new RuntimeException('Medicine name is required.');
        }

        if (strlen($medicineName) < 2 || strlen($medicineName) > 255) {
            throw new RuntimeException('Medicine name must be between 2 and 255 characters.');
        }

        if (!preg_match('/^[A-Za-z0-9 .,()\'\-\/+]+$/', $medicineName)) {
            throw new RuntimeException('Medicine name contains invalid characters.');
        }

        return $medicineName;
    }

    public static function validateDate($dateValue, $label = 'Date') {
        $dateValue = trim((string) $dateValue);

        if ($dateValue === '') {
            throw new RuntimeException($label . ' is required.');
        }

        $date = DateTime::createFromFormat('Y-m-d', $dateValue);
        $errors = DateTime::getLastErrors();

        if (!$date || $errors['warning_count'] > 0 || $errors['error_count'] > 0 || $date->format('Y-m-d') !== $dateValue) {
            throw new RuntimeException($label . ' must use YYYY-MM-DD format.');
        }

        return $dateValue;
    }

    public static function validateDateOrder($manufactureDate, $expiryDate) {
        if (strtotime($expiryDate) <= strtotime($manufactureDate)) {
            throw new RuntimeException('Expiry date must be after manufacture date.');
        }
    }

    public static function validatePackCount($packCount, $min = 1, $max = 5000) {
        $packCount = (int) $packCount;

        if ($packCount < $min) {
            throw new RuntimeException('Pack quantity must be at least ' . $min . '.');
        }

        if ($packCount > $max) {
            throw new RuntimeException('Pack quantity is too large for one request.');
        }

        return $packCount;
    }

    public static function validateVerificationCode($code, $label = 'Code') {
        $code = self::normalizeCode($code);

        if ($code === '') {
            throw new RuntimeException($label . ' cannot be empty.');
        }

        if (strlen($code) < 4 || strlen($code) > 120) {
            throw new RuntimeException($label . ' length is invalid.');
        }

        if (!preg_match('/^[A-Z0-9\-]+$/', $code)) {
            throw new RuntimeException($label . ' format is invalid.');
        }

        return $code;
    }

    public static function validateReportDescription($description) {
        $description = self::normalizeWhitespace($description);

        if ($description === '') {
            throw new RuntimeException('Description is required.');
        }

        if (strlen($description) < 15) {
            throw new RuntimeException('Description must be at least 15 characters.');
        }

        if (strlen($description) > 1000) {
            throw new RuntimeException('Description is too long.');
        }

        return $description;
    }
}
