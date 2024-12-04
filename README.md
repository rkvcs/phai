# PHAI
PHP :heart: AI - lib to help generate tests using AI

Here we will use [Pest](https://pestphp.com) and Ollama with [codellama](https://codellama.dev/about).

```sh
composer install rkvcs/phai
```

### Using
```sh
./vendor/bin/phai -h


./vendor/bin/phai g:test <PATHFILE>

# Example 
./vendor/bin/phai g:test ./src/MyFile.php

# Result 
>> reading file.
>> generating prompt.
>> connectiong with AI.
>> ./tests/Unit/MyFileTest.php generated
```