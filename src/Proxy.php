<?php

namespace Phai;

class Proxy {
    public function sendPrompt(string $prompt): string{
        $client = new \GuzzleHttp\Client([
            // Base URI is used with relative requests
            'base_uri' => 'http://localhost:11434',
            // You can set any number of default request options.
            'timeout'  => 20.0,
        ]);
        
        $response = $client->request('POST', '/api/generate', [
            'stream' => true,
            'body' => json_encode([
                "model" => "codellama:latest",
                "prompt" => $prompt,
            ])
        ]);
        
        $body = $response->getBody();
        $answer = "";
        $_res = "";
        
        while (!$body->eof()) {
            $_res .= $body->read(1024);
        }
        
        foreach(explode("\n", $_res) as $line){
            $lparse = json_decode($line, true);
            
            if($lparse != null && $lparse["done"] == false){
                $answer .= $lparse["response"];
            }
        }
        $answer = str_replace("```php", "", $answer);
        $answer = str_replace("```", "", $answer);
        $answer = trim($answer);

        return $answer;
    }
}