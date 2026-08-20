<?php
declare(strict_types=1);

function landing_content_defaults(): array
{
    return [
        'site' => [
            'brand' => 'Game Dev Portfolio',
            'nav_projects' => 'Projects',
            'nav_about' => 'About',
            'nav_contact' => 'Contact',
            'footer_name' => 'Game Dev Portfolio',
        ],
        'hero' => [
            'eyebrow' => 'Developer Portfolio',
            'title' => 'Making playable worlds.',
            'copy' => 'A home for games, prototypes, and the systems that bring them to life.',
            'button_text' => 'View Projects',
        ],
        'projects' => [
            'eyebrow' => 'Selected Work',
            'title' => 'Projects',
        ],
        'about' => [
            'eyebrow' => 'About',
            'title' => 'The Developer',
            'body' => 'Replace this short introduction with your development focus, tools, and the kind of games you build.',
        ],
        'contact' => [
            'eyebrow' => 'Contact',
            'title' => 'Let’s make something memorable.',
            'email' => 'hello@example.com',
        ],
    ];
}

function landing_content(array $storedContent): array
{
    $content = landing_content_defaults();
    foreach ($content as $section => $fields) {
        if (isset($storedContent[$section]) && is_array($storedContent[$section])) {
            $content[$section] = array_merge($fields, array_intersect_key($storedContent[$section], $fields));
        }
    }
    return $content;
}
