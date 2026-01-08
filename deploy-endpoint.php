<?php

use Illuminate\Support\Facades\Route;

// Deploy via pull+build na VPS
Route::post('/deploy/pull-build', function (\Illuminate\Http\Request $request) {
    $deployToken = config('app.deploy_token', 'wkcrm-deploy-2025');
    
    if ($request->header('X-Deploy-Token') !== $deployToken) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        $projectRoot = '/var/www/consultoriawk-crm';
        $appRoot = '/var/www/consultoriawk-crm/app';
        
        if (!is_dir($projectRoot)) {
            throw new \Exception("Project directory not found: {$projectRoot}");
        }

        $output = [];
        
        // Git pull
        $output[] = "=== Git Pull ===";
        exec("cd {$projectRoot} && git pull origin main 2>&1", $gitOutput);
        $output = array_merge($output, $gitOutput);
        
        // NPM build
        $output[] = "\n=== NPM Build ===";
        exec("cd {$projectRoot}/wk-customer-app && npm run build 2>&1", $npmOutput);
        $output = array_merge($output, array_slice($npmOutput, -20));
        
        // Copy dist
        $output[] = "\n=== Copying Dist ===";
        exec("cp -r {$projectRoot}/wk-customer-app/dist/* {$appRoot}/ 2>&1", $copyOutput);
        $output = array_merge($output, $copyOutput);
        
        $output[] = "\n✅ Deploy completed!";
        
        return response()->json([
            'success' => true,
            'message' => 'Deploy realizado com sucesso',
            'output' => implode("\n", $output)
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});
