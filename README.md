# Enigma

This is a solution for Concordia University's Engineering Week Coding Challenge 2017

This library can decrypt Rotor 1 and Rotor 2 even if all the letters are missing. It can easily be extended to solve missing letters in Rotor 3 or Reflector.

Challenge #1: Build an Enigma Machine and decrypt the given text. Please see [PDF Instructions](c1/C1.pdf) for more information.

Challenge #2: 10 Letters are missing from Rotor 1, try to find them and decrypt the given text. Please see [PDF Instructions](c2/C2.pdf) for more information.

Challenge #3: All the letters are missing from Rotor 2, try to find them and decrypt the given text. Please see [PDF Instructions](c3/C3.pdf) for more information.

## Usage
php main.php [challenge]

[challenge] can be 1, 2 or 3. If you do not specify a challenge, the script will run challenge 3

Configuration files (config.php) are in directories c1, c2 and c3 for each corresponding challenge. Feel free to modify the keys. Remember this library can solve Rotor 1 and Rotor 2 that are missing any number of letters

## Execution time
The average execution time on my laptop was:
 
Challenge #1: 0.11 seconds

Challenge #2: 0.09 seconds

Challenge #3: 0.12 seconds

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.