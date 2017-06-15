<?php

class Configuration extends Service
{
    public $data;
    
    private $env;
    private $configPath;
    private $parametersPath;
    private $settingsPath;
    
    public function onRegister()
    {
        parent::onRegister();
        
        // Find current environment
        
        $this->env = $this->getApp()->getEnvironment();
        
        // Load configuration
        
        $this->configPath = ROOT_DIR . '/config/config' . (empty($this->env) ? '' : '_' . $this->env) . '.php';
        $this->data       = include $this->configPath;
        
        // Load parameters
        
        $this->parametersPath = ROOT_DIR . '/config/parameters' . (empty($this->env) ? '' : '_' . $this->env) . '.php';
        $this->parameters     = include $this->parametersPath;
        
        // Merge
        
        $this->data = Utils::arrayMergeRecursive($this->data, $this->parameters);
        
        // Load application settings
        
        $this->settingsPath        = ROOT_DIR . '/config/' . $this->data['services']['configuration']['appSettingsFile'];
        $this->data['appSettings'] = $this->readAppSettings();
    }
    
    public function mergeAppSettings($data)
    {
        $this->data['appSettings'] = array_merge($this->data['appSettings'], $data);
    }
    
    public function updateParameters($data)
    {
        // Update parameters
        
        $this->parameters = Utils::arrayMergeRecursive($this->parameters, $data);
        
        // Merge
        
        $this->data = Utils::arrayMergeRecursive($this->data, $this->parameters);
        
        // Save updated
        
        $content  = "<?php\n\nreturn " . var_export($this->parameters, true) . ";\n\n?>";
        
        file_put_contents($this->parametersPath, $content);
    }
    
    public function updateAppSettings($data)
    {
        $this->mergeAppSettings($data);
        
        $content = '';
        
        foreach($this->data['appSettings'] as $key => $value)
        {
            $content .= "$key=$value\n";
        }
        
        $file = fopen($this->settingsPath, 'w');
        
        fwrite($file, $content);
        fclose($file);
    }
    
    private function readAppSettings()
    {
        $lines = file($this->settingsPath);
        
        $result = array();
        
        foreach($lines as $line)
        {
            $parts = explode('=', $line, 2);
            
            if(count($parts) === 2)
            {
                $result[$parts[0]] = trim($parts[1]);
            }
        }
        
        return $result;
    }
}

?>