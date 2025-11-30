<?php 

// ----------------------------
// 3. Enqueue admin CSS
// ----------------------------
add_action('admin_enqueue_scripts', function(){
    wp_enqueue_style('ahbn-admin-css', false);
    ?>
    <style>
    .ahbn-wrapper {display:flex; flex-direction:column; font-family:Arial,sans-serif;}
    .ahbn-container {display:flex; margin-top:20px;}
    .ahbn-sidebar {width:220px; background:#f1f1f1; padding:15px; border-radius:5px;}
    .ahbn-sidebar ul {list-style:none; padding:0;}
    .ahbn-sidebar ul li {margin-bottom:5px;}
    .ahbn-sidebar ul li a {display:flex; align-items:center; padding:10px; text-decoration:none; color:#333; border-radius:4px; transition:all 0.2s;}
    .ahbn-sidebar ul li a:hover, .ahbn-sidebar ul li a.ahbn-active {background:#0073aa;color:#fff;}
    .ahbn-sidebar ul li a span {margin-left:10px;}
    .ahbn-content {flex:1; background:#fff; padding:20px; border-radius:5px; box-shadow:0 0 10px rgba(0,0,0,0.05);}
    .ahbn-cards {display:flex; gap:15px; margin-bottom:20px;}
    .ahbn-card {flex:1; background:#0073aa; color:#fff; padding:15px; border-radius:5px; text-align:center;}
    .ahbn-card h3 {margin:0; font-size:20px;}
    .ahbn-card p {margin:5px 0 0 0; font-size:14px;}
    table.widefat {width:100%; border-collapse:collapse;}
    table.widefat th, table.widefat td {border:1px solid #ddd; padding:8px; text-align:left;}
    table.widefat tr:hover {background:#f1f1f1;}
    </style>
    <?php
});