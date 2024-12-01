<?php

namespace Phai;

use Reflector;
use Phai\Proxy as PProxy;
use stdClass;

class ParseFile {

    private string $namespace;
    private string $className;
    private string $finalFileName;
    private Reflector $reflaction;

    public function __construct(string $file_path)
    {
        $_content = file_get_contents($file_path);
        $_namespace = null;

        foreach(explode("\n", $_content) as $line){
            if(strpos($line, 'namespace') === 0){
                $_namespace = substr($line, 10, -1);
            }
        }
        $_split = explode("/", $file_path);

        $this->namespace = $_namespace;
        $this->className = substr($_split[count($_split)-1], 0, -4);
    }

    public function generate() {
        try {
            $proxy = new PProxy();
            yield [null, 'reading file.'];
            $this->reflaction = new \ReflectionClass($this->namespace."\\".$this->className);
            
            yield [null, 'generating prompt.'];
            $prompt = $this->generatePrompt($this->reflaction);
            
            yield [null, 'connectiong with AI.'];
            $answer = $proxy->sendPrompt($prompt);

            $finalFile = $this->generateTestFile($answer);
            yield [null, "$finalFile generated"];

        } catch (\Exception $err) {
            yield [$err, null];
        }
    }

    private function generatePrompt(Reflector $reflaction): string {
        $prompt = "";
        $prompt .= "Using the class ".($this->namespace."\\".$this->className)." with doc:".PHP_EOL;
        $prompt .= PHP_EOL.$reflaction->getDocComment().PHP_EOL;
        $prompt .= PHP_EOL."Write a unit tests with the framework PEST(https://pestphp.com/) for each method:".PHP_EOL.PHP_EOL;

        foreach($reflaction->getMethods() as $method){

            $prompt .= "- $method->name (";
            $lparams = [];

            foreach($method->getParameters() as $params){
                $lparams[] = $params->getType()." \$$params->name";
            }
            $prompt .= implode(", ", $lparams)."): ".$method->getReturnType().PHP_EOL;
            
            if($method->getDocComment()){
                $prompt .= "   For method $method->name use the comment:".PHP_EOL;
                $prompt .= "    ".$method->getDocComment().PHP_EOL;
            }
        }

        $prompt .= PHP_EOL."Give me only the code without explanation.";

        return $prompt;
    }

    private function generateTestFile(string $content): string {
        $path = explode("\\", $this->reflaction->name);
        $path_folder = implode("/", ['./tests/Unit', ...array_splice($path, 0, count($path) - 1 )]);

        if(is_dir($path_folder) == false){
            if(!mkdir(directory: $path_folder, recursive: true)){
                die("Failed to create directories and File [$path_folder]");
            }
        }
        $_final_file = $path_folder."/".$this->reflaction->getShortName()."Test.php";
        file_put_contents($_final_file, $content);

        return $_final_file;
    }
}
