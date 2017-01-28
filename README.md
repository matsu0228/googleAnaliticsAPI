# use google analytics API sample

## env

- phpenv 1.1.0
- php 7.1.0
- google-api-php-client(https://travis-ci.org/google/google-api-php-client)

## note

- set file '../google-api-data/google-api-const.php'

```

function getGoogleacountEmail() {
  return '***@developer.gserviceaccount.com';
}

function getFilelocation($google_root_dir) {
  return  $google_root_dir.'/../google-api-data/****.p12';
}

```
