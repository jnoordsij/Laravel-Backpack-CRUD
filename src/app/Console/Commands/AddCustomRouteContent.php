<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;

class AddCustomRouteContent extends Command
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:add-custom-route
                                {code : HTML/PHP code that registers a route. Use either single quotes or double quotes. Never both. }
                                {--route-file=routes/backpack/custom.php : The file where the code should be added relative to the root of the project. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add HTML/PHP code to the routes/backpack/custom.php file';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $routeFilePath = base_path($this->option('route-file'));
        // check if the file exists
        if (!file_exists($routeFilePath)) {
            $this->line("The route file  <fg=blue>$routeFilePath</> does not exist.");
            $createRouteFile = $this->confirm('Should we create the file in  <fg=blue>'.  $routeFilePath . '</> ?', 'yes');
            if($createRouteFile) {
                $this->call('vendor:publish', ['--provider' => \Backpack\CRUD\BackpackServiceProvider::class, '--tag' => 'custom_routes']);
            }else{
                $this->error('The route file does not exist. Please create it first.');
            }

        }

        $code = $this->argument('code');

        $this->progressBlock("Adding route to <fg=blue>$routeFilePath</>");

        $originalContent = file($routeFilePath);

        // clean the content from comments etc
        $cleanContent = $this->cleanContentArray($originalContent);

        // if the content contains code, don't add it again.
        if (array_search($code, $cleanContent, true) !== false) {
            $this->closeProgressBlock('Already existed', 'yellow');

            return;
        }

        // get the last element of the array contains '}'
        $lastLine = $this->getLastLineNumberThatContains('}', $cleanContent);

        if($lastLine === false) {
            $this->closeProgressBlock('Could not find the last line, file ' . $routeFilePath . ' may be corrupted.', 'red');
            return;
        }

        // add the code to the line before the last line
        array_splice($originalContent, $lastLine, 0, '    ' . $code.PHP_EOL);

        // write the new content to the file
        if(file_put_contents($routeFilePath, implode('', $originalContent)) === false) {
            $this->closeProgressBlock('Failed to add route. Failed writing the modified route file. Maybe check file permissions?', 'red');
            return;
        }

        $this->closeProgressBlock('done', 'green');
    }

    private function cleanContentArray(array $content)
    {
        return array_filter(array_map(function ($line) {
            $lineText = trim($line);
            if  ($lineText === '' ||
                $lineText === '\n' ||
                $lineText === '\r' ||
                $lineText === '\r\n' ||
                $lineText === PHP_EOL ||
                str_starts_with($lineText, '<?php') ||
                str_starts_with($lineText, '?>') ||
                str_starts_with($lineText, '//') ||
                str_starts_with($lineText, '/*') ||
                str_starts_with($lineText, '*/') ||
                str_ends_with($lineText, '*/') ||
                str_starts_with($lineText, '*') ||
                str_starts_with($lineText, 'use ') ||
                str_starts_with($lineText, 'return ') ||
                str_starts_with($lineText, 'namespace ')) {
                    return null;
            }
            return $lineText;
        }, $content));
    }

    /**
     * Parse the given file stream and return the line number where a string is found.
     *
     * @param  string  $needle  The string that's being searched for.
     * @param  array  $haystack  The file where the search is being performed.
     * @return bool|int The last line number where the string was found. Or false.
     */
    private function getLastLineNumberThatContains($needle, $haystack)
    {
        $matchingLines = array_filter($haystack, function ($k) use ($needle) {
            return strpos($k, $needle) !== false;
        });

        if ($matchingLines) {
            return array_key_last($matchingLines);
        }

        return false;
    }
}
