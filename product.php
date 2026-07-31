<?php
/**
 * TATVAM - Dynamic E-Book Landing Page Redirect
 */
$slug = filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS);

if ($slug === 'positive-thinking') {
    header('Location: positive-thinking.html');
    exit;
} else {
    header('Location: index.html');
    exit;
}