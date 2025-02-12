# Wepod PHP Class

This is an example of how to use the `wepod` class in PHP.

## Installation

1. Clone the repository:
    ```sh
    git clone https://github.com/mirabolfazl/wepod.git
    ```

2. Include the `wepod.php` file in your project:
    ```php
    include 'wepod.php';
    ```

## Usage

Here is an example of how to use the `wepod` class:

### Sign Up

```php
<?php
include 'wepod.php';

$phone = '09121234567';
$wepod = new wepod($phone);

// Sign up with the phone number
$wepod->signup($phone);
```

### Verify OTP

```php
<?php
include 'wepod.php';

$phone = '09121234567';
$wepod = new wepod($phone);

try {
    // Verify OTP
    $wepod->verifyotp(123456);
} catch (Throwable $e) {
    echo $e->getMessage();
    // Handle error during OTP verification
}
```

### Get Card Name

```php
<?php
include 'wepod.php';

$phone = '09121234567';
$wepod = new wepod($phone);

// Example for getting card name
$wepod->post = false;
echo $wepod->SmartTransfer->detectInputType(['Input' => '1234567887654321', 'InputType' => 0])->fullNames;
```

## Methods

### `signup(string $mobileNumber)`

Signs up a user with the given mobile number.

### `verifyotp(int $otpCode)`

Verifies the OTP code.

### `logout()`

Logs out the user.

### `refreshToken()`

Refreshes the access token.

### `request($method, $datas = [], $debug = false)`

Makes a request to the Wepod API.

## License

This project is licensed under the MIT License.