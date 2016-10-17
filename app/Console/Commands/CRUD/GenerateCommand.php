<?php

namespace App\Console\Commands\CRUD;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Artisan;

class GenerateCommand extends Command
{
    const NAMESPACE_SEPARATOR = "\\";
    const PATH_SEPARATOR = "/";
    const ROOT_NAMESPACE = "App";
    const REGISTER_PLACEHOLDER = '//@GeneratorRegisterPlaceholder_DoNotDelete';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud
        {modelName? : ModelName}
        {modelPluralName? : ModelName plural}
        {--modelNamespace=Models : Namespace relative to \\App\\}
        {--controllerNamespace=Admin : Namespace relative to \\App\\}
        {--migration=1}
        {--seeder=0}
        {--style=default : Style of MVC [default,plain,sortable]}
        {--relation=0 : If ModelName is filled, generates OneToMany Relation }
        {--force : Rewrite existing files}
        {--scripts=0 : Generate app module.js}
        {--register=1 : Register Route,RouteBindings,Policy,MenuItem & Scripts}
        {--author=brainz.cz}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates bAdmin CRUD MVC + Policy, DB Migration, DB Seeder & jsModule';

    protected $modelNamespace = null;
    protected $modelName = null;
    protected $modelPluralName = null;
    protected $controllerNamespace = null;
    protected $style= null;
    protected $seeder = null;
    protected $migrate = null;
    protected $scripts = null;
    protected $relation = null;
    protected $register = null;

    /**
     * @var Composer
     */
    protected $composer = null;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info($this->description);
        if(!$this->parseArguments()) {
            return;
        }
        $this->info('');

        $this->makeModel();
        $this->makeController();
        $this->makeViews();
        $this->makeScripts();
        $this->makeRequest();
        $this->makePolicy();
        $this->makeTranslations();
        $this->makeMigration();
        $this->makeMigrationRelation();
        $this->makeSeeder();
        $this->registerComponents() || $this->showToDo();
    }


    protected function parseArguments()
    {
        $interactive = false;
        $this->modelNamespace = $this->option('modelNamespace');
        $this->controllerNamespace = $this->option('controllerNamespace');

        $this->modelName = $this->argument('modelName');
        if(!$this->modelName) {
            $interactive = true;
            $this->modelName = $this->ask('Please provide model name (Item)');
        }
        if(!$this->modelName) {
            $this->error('ModelName is required argument');
            return false;
        }

        $this->modelPluralName = $this->argument('modelPluralName');
        if($interactive && !$this->modelPluralName) {
            $defaultModelPlural = str_plural($this->getModelName());
            $this->modelPluralName = $this->ask('Please provide model name in plural',$defaultModelPlural);
        }
        if(!$this->modelPluralName) {
            $this->modelPluralName = str_plural($this->getModelName());
        }

        $this->style = $this->option('style');
        if($interactive && $this->style == 'default') {
            $this->style = $this->choice('Select style of generated files',['default','plain','sortable'],0);
        }

        $this->relation = $this->option('relation');
        if($interactive && !$this->relation) {
            $this->relation = $this->ask('Please provide relation model name (optional)','0');
        }

        $this->migrate = (bool)$this->option('migration');
        if($interactive && !$this->migrate) {
            $this->migrate = (bool)$this->confirm('Do you wish to generate DB Migrations?');
        }

        $this->seeder = (bool)$this->option('seeder');
        if($interactive && !$this->seeder) {
            $this->seeder = (bool)$this->confirm('Do you wish to generate DB Seeder?');
        }

        $this->scripts = (bool)$this->option('scripts');
        if($interactive && !$this->scripts) {
            $this->scripts = (bool)$this->confirm('Do you wish to generate app module.js?');
        }

        $this->register = $this->option('register');
        if($interactive && !$this->registerComponents()) {
            $this->register = (bool)$this->confirm('Do you want register generated components?');
        }

        if($interactive) {
            $this->info('/////////////////////// CODE GENERATION STARTED ////////////////////////////');
        }

        return true;
    }


    public function registerComponents(){
        if($this->register) {
            $this->info('Automatic registration of components');

            $ns = lcfirst($this->controllerNamespace);
            $Ns = ucfirst($this->controllerNamespace);

            //register Route
            if($ns) {
                $filePath = app_path("Http/Routes/{$ns}Routes.php");
            } else {
                $filePath = app_path("Http/routes.php");
            }
            $code = sprintf("Route::resource('%s','%sController');\n\n    ",
                $this->getEntityPluralName(),
                $this->getModelPluralName());
            $this->registerComponent($filePath,$code);

            //register RouteBinding
            $filePath = app_path('Providers/RouteServiceProvider.php');
            $code = sprintf("\$router->model('%s',\\%s::class);\n        ",
                $this->getEntityPluralName(),
                $this->getModelNameWithNamespace());
            $this->registerComponent($filePath,$code);

            //register MenuItem
            $filePath = app_path('Services/'.$Ns.'MenuFactory.php');
            $code = sprintf("\$menu->add(trans('%s.menu'),['route' => 'admin.%s.index'])
                ->data('icon','fa fa-cube')
                ->data('allow', \\Gate::allows('index',new \\%s()) );
\n            ",
                $this->getMessagesTerm(),
                $this->getEntityPluralName(),
                $this->getModelNameWithNamespace());
            $this->registerComponent($filePath,$code);

            //Register Policy
            $filePath = app_path('Providers/AuthServiceProvider.php');
            $code = sprintf("\\%s::class => \\%s::class,\n        ",
                $this->getModelNameWithNamespace(), $this->getPolicyNameWithNamespace());
            $this->registerComponent($filePath,$code);

            //Register js module
            if($this->scripts) {
                $filePath = resource_path($ns ? "assets/$ns/js/app.js" : 'assets/js/app.js');
                $code = sprintf("%s: require('./app/%s'),\n    ",
                    $this->getEntityPluralName(),
                    $this->getEntityPluralName());
                $this->registerComponent($filePath, $code);
            }

            //DB Seeder
            if($this->seeder) {
                $filePath = database_path('seeds/DatabaseSeeder.php');
                $code = sprintf("\$this->call(%sSeeder::class);\n        ", $this->getModelName());
                $this->registerComponent($filePath, $code);
            }

            //Sortable
            if($this->style == 'sortable') {
                $filePath = config_path('sortable.php');
                $code = sprintf("'%s' => \\%s::class,\n        ",
                    $this->getEntityPluralName(),
                    $this->getModelNameWithNamespace());
                $this->registerComponent($filePath,$code);
            }

            $this->info('');
            return true;
        }
        return false;
    }

    public function registerComponent($filePath,$code){
        $content = file_get_contents($filePath);
        if($already = str_contains($content,$code)) {
            $this->info(sprintf(' - %s component is already registered',$filePath));
        } else {
            $registered = str_replace(self::REGISTER_PLACEHOLDER, $code . self::REGISTER_PLACEHOLDER, $content);
            if (file_exists($filePath) && $registered != $content) {
                file_put_contents($filePath, $registered);
                $this->info(sprintf(' - %s updated', $filePath));
            } else {
                $this->error(sprintf(' - unable to register in "%s"', $filePath));
            }
        }
    }

    protected function saveFile($filePath,$fileContent){
        if(file_exists($filePath)) {
            if($this->option('force')) {
                $this->saveFileForce($filePath,$fileContent);
                $this->warn(sprintf('Rewriting %s ',$filePath));
            } else {
                $this->error(sprintf('File %s exists!',$filePath));
                return false;
            }
        } else {
            $this->saveFileForce($filePath,$fileContent);
        }
        return true;
    }

    protected function saveFileForce($filePath,$fileContent) {
        file_put_contents($filePath,$fileContent);
    }

    public function getStubContent($stubName){
        $contents = file_get_contents(__DIR__.'/stubs/'.$this->style."/".$stubName.'.stub');
        $contents = $this->substitution($contents);
        return $contents;
    }

    public function getViewContent($view){
        $contents = file_get_contents(__DIR__.'/views/'.$this->style."/".$view);
        $contents = $this->substitution($contents);
        return $contents;
    }

    public function getTranslationContent($locale){
        $contents = file_get_contents(__DIR__.'/lang/'.$locale.'/messages.stub');
        $contents = $this->substitution($contents);
        return $contents;
    }

    public function substitution($string){
        $string = str_replace("@entities",$this->getEntityPluralName(),$string);
        $string = str_replace("@entity",$this->getEntityName(),$string);
        $string = str_replace("@ModelNamespace",$this->getModelNamespace(),$string);
        $string = str_replace("@ControllerNamespace",$this->getControllerNamespace(),$string);
        $string = str_replace("@RequestNamespace",$this->getRequestNamespace(),$string);
        $string = str_replace("@Models",$this->getModelPluralName(),$string);
        $string = str_replace("@Model",$this->getModelName(),$string);
        $string = str_replace("@messages", $this->getMessagesTerm(),$string);
        $string = str_replace("@Relation", $this->getRelationModelName(),$string);
        $string = str_replace("@relations", lcfirst($this->getRelationModelPluralName()),$string);
        $string = str_replace("@Relations", $this->getRelationModelPluralName(),$string);
        $string = str_replace("@relation", lcfirst($this->getRelationModelName()),$string);
        $string = str_replace("@_author", $this->option('author'),$string);
        return $string;
    }

    protected function makeModel()
    {
        $fileContent = $this->getStubContent('Model');
        $filePath = $this->getModelFilePath();
        $result = $this->saveFile($filePath,$fileContent);
        if ($result) {
            $this->info(sprintf('Model "%s" was created', $this->getModelNameWithNamespace()));
        }
        $this->info('');
    }

    protected function makeController() {
        $fileContent = $this->getStubContent('Controller');
        $filePath = $this->getControllerFilePath();
        $result = $this->saveFile($filePath,$fileContent);
        if($result) {
            $this->info(sprintf('Controller "%s" was created', $this->getControllerNameWithNamespace()));
        }
        $this->info('');
    }

    protected function makePolicy() {
        $fileContent = $this->getStubContent('Policy');
        $filePath = $this->getPolicyFilePath();
        $result = $this->saveFile($filePath,$fileContent);
        if($result) {
            $this->info(sprintf('Policy "%s" was created', $this->getPolicyNameWithNamespace()  ));
        }
        $this->info('');
    }

    public function makeRequest(){
        $fileContent = $this->getStubContent('Request');
        $filePath = $this->getRequestFilePath();
        $result = $this->saveFile($filePath,$fileContent);
        if($result) {
            $this->info(sprintf('FormRequest "%s" was created', $this->getRequestNameWithNamespace()));
        }
        $this->info('');
    }

    public function makeTranslations(){

        $locales = collect(scandir(__DIR__."/lang"))->filter(function($dir){
            return !in_array($dir,['.','..']);
        });
        foreach ($locales as $locale) {
            @mkdir(resource_path('lang/'.$locale));
            $fileContent = $this->getTranslationContent($locale);
            $resourcePath = 'lang/'.$locale.'/'. $this->getTranslationDomain(). $this->getEntityPluralName().".php";
            $filePath = resource_path($resourcePath);
            $result = $this->saveFile($filePath,$fileContent);
            if($result) {
                $this->info(sprintf('Translations "%s" was created',$resourcePath));
            }
        }

        $this->info('');
    }

public function makeViews(){

        $views = collect(scandir(__DIR__."/views/".$this->style))
            ->filter(function($file){ return str_contains($file,'.php');   });
        foreach ($views as $view) {
            $basePath = 'views/'. lcfirst($this->controllerNamespace);
            @mkdir(resource_path( $basePath));
            @mkdir(resource_path( $basePath."/".$this->getEntityPluralName()));
            $fileContent = $this->getViewContent($view);
            $viewPath = $basePath ."/".$this->getEntityPluralName()."/".$view;
            $filePath = resource_path($viewPath);
            $result = $this->saveFile($filePath,$fileContent);
            if($result) {
                $this->info(sprintf('View "%s" was created',$viewPath));
            }
        }

        $this->info('');
    }

    public function makeScripts(){
        if($this->scripts) {
            $fileContent = $this->getStubContent('scripts');
            $filePath = $this->getScriptFilePath();
            $result = $this->saveFile($filePath, $fileContent);
            if ($result) {
                $this->info(sprintf('JS Module "%s" was created', $filePath));
            }
            $this->info('');
        }
    }




    protected function makeMigration()
    {
        if($this->migrate) {
            $fileContent = $this->getStubContent('migration');
            $migrationName = 'create_' . $this->getEntityPluralName() . '_table';
            $migrationPath = 'migrations/'.date('Y_m_d_000000')."_".$migrationName.'.php';
            $filePath = database_path($migrationPath);
            $result = $this->saveFile($filePath,$fileContent);
            $this->composer->dumpAutoloads();
            if($result) {
                $this->info(sprintf('DB Migration "%s" was created', $migrationPath ));
            }

            if (false && $this->option('migration')) {
                Artisan::call('make:migration', [
                    'name'     => $migrationName,
                    '--create' => $this->getEntityPluralName()
                ]);
            }

            $this->info('');
        }
    }

    protected function makeMigrationRelation()
    {
        if($this->migrate && $this->shouldGenerateRelation()) {
            $relationModel = $this->getRelationModelName();
            $fileContent = $this->getStubContent('migrationRelation');
            $migrationName = 'create_' . $this->getEntityName() ."_". lcfirst($relationModel). '_table';
            $migrationPath = 'migrations/'.date('Y_m_d_000100')."_".$migrationName.'.php';
            $filePath = database_path($migrationPath);
            $result = $this->saveFile($filePath,$fileContent);
            $this->composer->dumpAutoloads();
            if($result) {
                $this->info(sprintf('DB Migration "%s" was created', $migrationPath ));
            }
            $this->info('');
        }
    }

    protected function makeSeeder()
    {
        if($this->seeder) {
            $fileContent = $this->getStubContent('Seeder');
            $seederFilename = 'seeds/'.$this->getModelName()."Seeder.php";
            $filePath = database_path($seederFilename);
            $result = $this->saveFile($filePath,$fileContent);
            $this->composer->dumpAutoloads();
            if($result) {
                $this->info(sprintf('DB Seeder "%s" was created', $seederFilename ));
            }


            if (false) {
                Artisan::call('make:seeder', ['name' => $this->getModelName() . "Seeder"]);
            }

            $this->info('');
        }
    }

    protected function showToDo() {
        $contents = $this->getStubContent('todo');
        $this->warn('!!! You have to register your new components');
        $this->info('you can run command with "--register" option');
        $this->info($contents);
        $this->info('');
    }


    // ------------------ GETTERS ---------------------


    public function getModelName(){
        return ucfirst($this->modelName);
    }

    public function getModelPluralName(){
        return ucfirst($this->modelPluralName);
    }

    public function getEntityName(){
        return lcfirst($this->modelName);
    }

    public function getEntityPluralName() {
        return lcfirst($this->getModelPluralName());
    }

    public function getRelationModelName(){
        if ( $relation = $this->relation ) {
            return ucfirst($relation);
        }
        return "User"; //Default relation is User
    }

    public function getRelationModelPluralName(){
        $singular = $this->getRelationModelName();
        return ucfirst(str_plural($singular));
    }

    public function shouldGenerateRelation(){
        return (bool)$this->relation;
    }

    public function getModelNameWithNamespace(){
        $modelNs = $this->getModelNamespace();
        return ($modelNs ? $modelNs .self::NAMESPACE_SEPARATOR : '')
        .$this->getModelName();
    }

    public function getModelFilePath(){
        if ( $this->modelNamespace) {
            return app_path($this->modelNamespace . self::PATH_SEPARATOR . $this->getModelName() . '.php');
        } else {
            return app_path($this->getModelName() . '.php');
        }
    }

    public function getModelNamespace(){
        if( $this->modelNamespace) {
            return self::ROOT_NAMESPACE .self::NAMESPACE_SEPARATOR
                .$this->modelNamespace;
        }
        return self::ROOT_NAMESPACE;
    }

    public function getControllerNameWithNamespace(){
        return $this->getControllerNamespace()
        . self::NAMESPACE_SEPARATOR
        . $this->getModelPluralName()."Controller" ;
    }

    public function getControllerNamespace(){
        return self::ROOT_NAMESPACE .self::NAMESPACE_SEPARATOR
        ."Http". self::NAMESPACE_SEPARATOR
        ."Controllers"
        . ( $this->controllerNamespace ? self::NAMESPACE_SEPARATOR.$this->controllerNamespace : '' );
    }

    public function getControllerFilePath(){
        return app_path( "Http". self::PATH_SEPARATOR
            ."Controllers" . self::PATH_SEPARATOR
            . ( $this->controllerNamespace ? $this->controllerNamespace. self::PATH_SEPARATOR : '' )
            . $this->getModelPluralName()."Controller.php"  );
    }


    public function getRequestNameWithNamespace(){
        return $this->getRequestNamespace() . self::NAMESPACE_SEPARATOR . $this->getModelName()."Request";
    }

    public function getRequestNamespace(){
        return self::ROOT_NAMESPACE .self::NAMESPACE_SEPARATOR
        ."Http". self::NAMESPACE_SEPARATOR
        ."Requests"
        . ( $this->controllerNamespace ? self::NAMESPACE_SEPARATOR.$this->controllerNamespace : '' );
    }

    public function getRequestFilePath(){
        return app_path("Http". self::PATH_SEPARATOR
        ."Requests" . self::PATH_SEPARATOR
        . ( $this->controllerNamespace ? $this->controllerNamespace.self::PATH_SEPARATOR : '' )
        . $this->getModelName()."Request.php");
    }

    public function getPolicyNameWithNamespace(){
        return self::ROOT_NAMESPACE .self::NAMESPACE_SEPARATOR
        ."Policies". self::NAMESPACE_SEPARATOR
        . $this->getModelName()."Policy";
    }

    public function getPolicyFilePath(){
         return app_path("Policies". self::PATH_SEPARATOR
            . $this->getModelName()."Policy.php" );
    }

    /**
     * @return string
     */
    protected function getTranslationDomain()
    {
        return lcfirst($this->controllerNamespace) . "_";
    }

    public function getScriptFilePath(){
        return resource_path("assets". self::PATH_SEPARATOR
            .($this->controllerNamespace ? lcfirst($this->controllerNamespace) . self::PATH_SEPARATOR : '')
            . 'js' . self::PATH_SEPARATOR
            . 'app' .self::PATH_SEPARATOR
            . $this->getEntityPluralName().".js");
    }

    /**
     * @return string
     */
    protected function getMessagesTerm()
    {
        return $this->getTranslationDomain() . $this->getEntityPluralName();
    }

}
