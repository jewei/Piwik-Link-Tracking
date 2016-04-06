# Piwik-Link-Tracking
Dirty single file solution to Piwik tracking outbound link.

This is an onhand task to quickly implement Piwik tracking. It does not really follow separation of concerns principle. I would have the config code separately and probably everything else lives in a namespaced class, and comes with unit tests.

## Usage
Configure these constants to start:
* CONST_SITE_ID
* CONST_TOKEN_AUTH
* CONST_API_URL
* CONST_PIWIK_LIB_PATH
