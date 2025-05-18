# Annotation Platform

## Installation Manual

- Start MySQL server (XAMPP) or cli
    on 127.0.0.1 user root

- run in root folder
    `php bin/migrate.php`

- run in root folder
    `php -S localhost:8000`

## Project Structure

```txt
    .
├── README.md    
├── assets           #public files
│   ├── css
│   │   ├── reset.css
│   │   └── style.css
│   ├── images
│   │   ├── Logo.svg
│   │   ├── image_placeholder.webp
│   │   └── user-placeholder.webp
│   └── js
├── bin
│   └── migrate.php  #migration script
├── config
│   ├── config.php    #config specifying constants
│   └── database.php #DB object class
├── includes         #import parts for each page
│   ├── footer.php 
│   └── header.php
├── index.php        #entry point
├── login.php        #login page
├── logout.php
├── models           #main classes
│   ├── Image.php   
│   └── User.php
├── profile.php
├── register.php
├── upload-image.php
├── uploads          #uploaded files
│   └── images
│       ├── 02.webp
│       ├── 03.webp
│       ├── 07.webp
│       ├── 09.png
│       └── image_placeholder.webp
└── uploads.php
```
