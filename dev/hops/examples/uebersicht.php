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

            .semester {
                clear: both;
            }

                .semester .square {
                    width: 200px;
                    height: 200px;
                    float: left;
                    margin: 5px;
                    padding: 5px;
                    position: relative;
                }

                .semester .square.simple {
                    border: 1px solid #dd1166;
                }

                    .semester .square.simple h2 {
                        text-transform: uppercase;
                        color: #77cc00;
                    }

                .semester .square.module {
                    background: #b4a7a0;
                    color: #fff;
                }
                
                    .semester .square.module h2 {
                        font-weight: bold;
                        font-size: 11px;
                    }

                    .semester .square.module a {
                        color: #fff;
                        text-decoration: none;
                        border-bottom: 1px dotted #fff;
                    }
                    
                    .semester .square.module a:hover {
                        color: #fff;
                        text-decoration: none;
                        border-bottom: 1px solid #d16;
                    }
                    
                    .semester .square.module .module_infos {
                        margin-top: 20px;
                    }
                    
                    .semester .square.module a.details {
                        color: #d16;
                        border: 1px solid transparent;
                        position: absolute;
                        display: block;
                        bottom: 5px;
                        right: 5px;
                        margin-right: 5px;
                    }
                    
                    .semester .square.module a.details:hover {
                        border-bottom: 1px solid #d16;
                    }
        </style>
    </head>
    <body>

    <?php require('inc/uebersicht_veranstaltungen.inc.php'); ?>

    </body>
</html>
