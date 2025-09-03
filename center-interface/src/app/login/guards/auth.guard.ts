import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { LoginService } from '../services/login.service';
import { User } from '../../interface/user.interface';

export function isLogged(): CanActivateFn {
    return () => {
        const oauthService: LoginService = inject(LoginService);
        const router: Router = inject(Router);
        if (oauthService.isTokenExpire()) {

            router.navigate(['/login']);
            oauthService._isLogged$.next(false)
            return false;
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
    return () => {
        var accessOK = false
        const oauthService: LoginService = inject(LoginService);
        const router: Router = inject(Router)
        // oauthService.userConnected$.subscribe({
        //     next: (u) => {
        //         if (u !== null) {
        //             if (u.roles.some(r => role.includes(r))) {
        //                 accessOK = true
        //             } else {
        //                 router.navigate(["/"]);
        //                 accessOK = false
        //             }
        //         } else {
        //             router.navigate(["/"]);
        //             accessOK = false
        //         }
        //     },
        //     error: (e: Error) => {
        //         console.log(e.message)
        //         accessOK = false
        //     }
        // })

        return accessOK
    }
}

