<?php

// Migrating all `comment (fanwall)` to `fanwall`
$migrationFanWall = __get("migration_fanwall_4-16-12");
if ($migrationFanWall !== "done") {
    $queries = [
        "INSERT INTO fanwall ('value_id', 'radius') 
        SELECT 'value_id', 'radius' FROM comment_radius;
        JOIN application_option_value ON application_option_value.value_id = comment_radius.value_id
        JOIN application_option ON application_option.option_id = application_option_value.option_id
        WHERE application_option.code = 'fanwall';",

        "INSERT INTO fanwall_post ('post_id', 'value_id', 'customer_id', 'title', 'subtitle', 'text', 'image', 'date', 'is_visible', 'latitude', 'longitude', 'flag', 'created_at', 'updated_at', 'created_at_utc', 'updated_at_utc') 
        SELECT 'comment_id', 'value_id', 'customer_id', 'title', 'subtitle', 'text', 'image', 'date', 'is_visible', 'latitude', 'longitude', 'flag', 'created_at', 'updated_at' FROM comment
        JOIN application_option_value ON application_option_value.value_id = comment.value_id
        JOIN application_option ON application_option.option_id = application_option_value.option_id
        WHERE application_option.code = 'fanwall';",

        "INSERT INTO fanwall_post_comment ('comment_id', 'post_id', 'customer_id', 'text', 'flag', 'is_visible', 'created_at', 'updated_at') 
        SELECT 'answer_id', 'comment_id', 'customer_id', 'text', 'flag', 'is_visible', 'created_at', 'updated_at'
        FROM comment_answer
        JOIN comment ON comment.comment_id = comment_answer.comment_id
        JOIN application_option_value ON application_option_value.value_id = comment.value_id
        JOIN application_option ON application_option.option_id = application_option_value.option_id
        WHERE application_option.code = 'fanwall';",

        "INSERT INTO fanwall_post_like ('like_id', 'post_id', 'customer_id', 'customer_ip', 'user_agent' ) 
        SELECT 'like_id', 'comment_id', 'customer_id', 'customer_ip', 'user_agent' 
        FROM comment_like
        JOIN comment ON comment.comment_id = comment_like.comment_id
        JOIN application_option_value ON application_option_value.value_id = comment.value_id
        JOIN application_option ON application_option.option_id = application_option_value.option_id
        WHERE application_option.code = 'fanwall';",
    ];

    foreach ($queries as $query) {
        try {
            $this->query($query);
        } catch (\Exception $e) {
            // Silent!
        }
    }

    __set("migration_fanwall_4-16-12", "done");
}