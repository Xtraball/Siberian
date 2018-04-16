<?php

// Clean-up unwanted log file
if (is_file('/tmp/debug.log')) {
    unlink('/tmp/debug.log');
}