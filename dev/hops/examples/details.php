<!DOCTYPE html>
<html>
    <head>
        <title>Übersicht Veranstaltungen</title>
        <meta charset="utf-8" />

        <style>
            * {
                font-size: 13px;
                font-weight: 400;
                font-family: 'myriad-pro', Geneva, Arial, Tahoma, Verdana, sans-serif;
                line-height: 15px;
                margin: 0;
            }

            body > div {
                margin: 50px 0 0 50px;
            }

            h1 {
                background: #d16;
                color: #fff;
                font-size: 11px;
                font-weight: bold;
                padding: 2px;
                padding-left: 5px;
                padding-right: 5px;
                text-transform: uppercase;
                margin-bottom: 15px;
                float: left;
                letter-spacing: 1px;
            }

            .module_infos {
                clear: both;
            }

            .entry {
                margin: 20px 0;
            }

            .entry .title {
                font-size: 11px;
                color: #77cc00;
                float: left;
                width: 250px;
            }

            .entry .text {
                float: left;
                width: 516px;
            }

            .entry .text * {
                margin-bottom: 15px;
            }

            .clearfix {
                clear: both;
            }

        </style>
    </head>
    <body>

    <?php require('inc/details_veranstaltung.inc.php'); ?>

    </body>
</html>
