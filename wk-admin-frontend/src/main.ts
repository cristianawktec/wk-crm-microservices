import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import { AppModule } from './app.module';

console.log('🔐 [MAIN.TS] Angular Bootstrap iniciando - ', new Date().toISOString());
console.log('🔐 [MAIN.TS] Current URL:', window.location.href);
console.log('🔐 [MAIN.TS] localStorage size:', Object.keys(localStorage).length);

platformBrowserDynamic().bootstrapModule(AppModule)
  .catch(err => console.error(err));
