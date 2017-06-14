# Testing Configuration

The tests in this folder make actual calls to a live Canvas API instance. The configuration steps that I have taken to set up these calls are the following:

  1. I have created a number of calendar events in that user's calendar in June 2017 (this allows the tests to reliably query an array of values of some size -- otherwise difficult to ensure)

  2. I have configured several environment variables that are accessed by the tests, as described below.

#### Bash Environment

My local configuration in `.bashrc` or similar.

```bash
export CANVASPEST_URL="https://canvas.instructure.com/api/v1" # user your fave
export CANVASPEST_TOKEN="hexadecimal-gesundheit" # use a real token, obv.
export CANVASPEST_USER_ID="12345" # ID of user to whom token was issued
export CANVASPEST_ITEMS="10" # the number of calendar events in June '17
```

#### Scrutinizer YML Configuration

Hand-entered in my [Scrutinizer](https://scrutinizer-ci.com/g/smtech/canvaspest/) repo configuration (Settings > Configuration).

```YML
build:
    environment:
        variables:
            CANVASPEST_URL: 'https://canvas.instructure.com/api/v1'
            CANVASPEST_TOKEN: 'hexadecimal-gesundheit'
            CANVASPEST_USER_ID: '12345'
            CANVASPEST_ITEMS: '10'
```
