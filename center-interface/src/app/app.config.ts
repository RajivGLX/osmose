import { ApplicationConfig, LOCALE_ID } from '@angular/core';
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { AuthInterceptor } from './login/interceptors/AuthInterceptor';
import { JWT_OPTIONS, JwtHelperService, JwtModule } from '@auth0/angular-jwt';
import localeFr from '@angular/common/locales/fr';
import { APP_BASE_HREF, registerLocaleData } from '@angular/common';
import { MatPaginatorIntl } from "@angular/material/paginator";
import { CustomMatPaginator } from "./shared/custom-classes/customMatPaginator";
registerLocaleData(localeFr, 'fr-FR');

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(routes),
    provideAnimationsAsync(),
    provideHttpClient(withInterceptors([AuthInterceptor])),
    JwtHelperService,
    { provide: JWT_OPTIONS, useValue: JWT_OPTIONS }, provideAnimationsAsync(), provideAnimationsAsync(), provideAnimationsAsync(),
    { provide: LOCALE_ID, useValue: 'fr-FR' },
    { provide: APP_BASE_HREF, useValue: '/' },
    { provide: MatPaginatorIntl, useClass: CustomMatPaginator }
  ]
};

