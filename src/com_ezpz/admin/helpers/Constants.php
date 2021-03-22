<?php

use Ezpizee\ConnectorUtils\Client;

class Constants
{
    const DB_TB_EZPZ = "#__ezpz";
    const KEY_SCHEMA = "schema";
    const API_CONFIG_KEYS = [
        Client::KEY_CLIENT_ID,
        Client::KEY_CLIENT_SECRET,
        Client::KEY_APP_NAME,
        Constants::KEY_SCHEMA,
        Client::KEY_ENV
    ];
}