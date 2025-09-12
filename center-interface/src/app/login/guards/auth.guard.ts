import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { LoginService } from '../services/login.service';
import { User } from '../../interface/user.interface';
import { catchError, map, Observable, of } from "rxjs";

export function isLogged(): CanActivateFn {
    return () => {
        const oauthService: LoginService = inject(LoginService);
        const router: Router = inject(Router);
        const nav = router.getCurrentNavigation();
        const target = nav?.extractedUrl?.queryParams?.['target']
        if(target == 'activate-account'){
            return router.parseUrl('/activate-account/'+nav?.extractedUrl?.queryParams['token']);
        }
        if(target == 'reset-password'){
            return router.parseUrl('/reset-password/'+nav?.extractedUrl?.queryParams['token']+ '/' +nav?.extractedUrl?.queryParams['context']);
        }
        if (oauthService.isTokenExpire()) {
            oauthService._isLogged$.next(false)
            return router.parseUrl('/login');

        }
        oauthService._isLogged$.next(true)
        return true;
    }
}

export function isGuest(): CanActivateFn {
    return () => {
        const oauthService: LoginService = inject(LoginService);
        const router: Router = inject(Router)
        if (oauthService.isTokenExpire()) {
            return true;
        }
        router.navigate(["/login"]);
        return false;
    }
}

export function hasAccess(role: string[]): CanActivateFn {
    return (): Observable<boolean> => {
        const oauthService = inject(LoginService);
        const router = inject(Router);
        const user = oauthService._userConnected$.getValue();

        if (user) {
            if (user.roles.some(r => role.includes(r))) {
                return of(true);
            } else {
                router.parseUrl('/');
                return of(false);
            }
        } else {
            return oauthService.userConnected$.pipe(
                map((value: User | null) => {
                    if (value) {
                        if (value.roles.some(r => role.includes(r))) {
                            return true;
                        } else {
                            router.parseUrl('/');
                            return false;
                        }
                    } else {
                        router.parseUrl('/');
                        return false;
                    }
                }),
                catchError(error => {
                    router.parseUrl('/');
                    return of(false);
                })
            );
        }
    }
}

