import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { JwtHelperService } from '@auth0/angular-jwt';
import { BehaviorSubject, Observable } from 'rxjs';
import { environment } from '../../../environment/environment.development';
import { User } from '../../interface/user.interface';
import { Center } from '../../interface/center.interface';
import { ToolsService } from '../../shared/services/tools.service';
import { Region } from '../../interface/region.interface';

@Injectable({
    providedIn: 'root'
})

export class LoginService {

    public _isLogged$ = new BehaviorSubject<boolean>(false)
    get isLogged$() {
        return this._isLogged$.asObservable()
    }

    public _userConnected$ = new BehaviorSubject<User | null>(null)
    get userConnected$() {
        return this._userConnected$.asObservable()
    }

    public _allRegions$ = new BehaviorSubject<Array<Region> | null>(null)
    get allRegions$() {
        return this._allRegions$.asObservable()
    }

    private _loading$ = new BehaviorSubject<boolean>(false)
    get loading$() {
        return this._loading$.asObservable()
    }

    private _errorMsg$ = new BehaviorSubject<string>('')
    get errorMsg$() {
        return this._errorMsg$.asObservable()
    }

    private _successMsg$ = new BehaviorSubject<string>('')
    get successMsg$() {
        return this._successMsg$.asObservable()
    }

    constructor(
        private http: HttpClient,
        private jwtHelper: JwtHelperService,
        private router: Router,
        private toolsService: ToolsService
    ) { }


    sendCredentials(credentials: any) {
        this._loading$.next(true)
        const headers = { 'Content-type': 'application/json' }
        const infosLog = { username: credentials.username, password: credentials.password }
        this.http.post(environment.apiURL + '/api/login_check', infosLog, { headers }).subscribe({
            next: (data: any) => {
                this._loading$.next(false)
                sessionStorage.setItem('token-auth', data.token);
                this._isLogged$.next(true)
                console.log(data)
                this.getConnectedUser()
                this._successMsg$.next('Connexion réalisée')
                this.router.navigateByUrl('/')
                // console.log(this.jwtHelper.decodeToken(data.token))
            },
            error: (err: any) => {
                this._loading$.next(false)
                switch (err.status) {
                    case 401:
                        this.toolsService.openSnackBar('Identifiants invalides', false)
                        break
                    case 403:
                        this.toolsService.openSnackBar('vous n\'êtes pas autorisés à acceder à cette page', false)
                        break
                    case 500:
                        this.toolsService.openSnackBar('Une erreur serveur est survenue', false)
                        break
                    default:
                        this.toolsService.openSnackBar('Une erreur est survenue', false)
                }
            }
        })
    }

    isTokenExpire() {
        return this.jwtHelper.isTokenExpired(sessionStorage.getItem('token-auth'));
    }

    logout() {
        sessionStorage.removeItem('token-auth');
        this._isLogged$.next(false)
        this._userConnected$.next(null)
        this.router.navigateByUrl('/login')
    }

    isLoggedIn() {
        if (this.isTokenExpire() == false) {
            return true;
        } else {
            return false;
        }
    }

    sendMailForgotPassword(email: string) {
        return this.http.post(environment.apiURL + '/reset-password-mail', email);
    }

    resetPassword(token: string, resetPasswordForm: any) {
        let credentials = {
            token: token,
            newPassword: resetPasswordForm.newPassword
        }
        return this.http.post(environment.apiURL + '/valid-new-password', credentials);
    }

    getConnectedUser() {
        this.http.get<User>(environment.apiURL + '/api/user-connect').subscribe({
            next: (value: User) => {
                if (value.roles.includes("ROLE_ADMIN_DIALYZONE")) {
                    value.adminDialyzone = true;
                    this._userConnected$.next(value);
                }else{
                    value.adminDialyzone = false;
                    this._userConnected$.next(value);
                }
                console.log(this._userConnected$.value)

            }, error: (e: Error) => {
                console.log(e)
                this.toolsService.openSnackBar('Session expirée. Veuillez vous reconnecter.', false);
                this._isLogged$.next(false);
                this._userConnected$.next(null);
                sessionStorage.removeItem('token-auth');
                this.router.navigateByUrl('/login');
            }
        })
    }

    getAllRegions(forceReload: boolean = false) {
        if (this._allRegions$.value && !forceReload) return
        this.http.get<Array<Region>>(environment.apiURL + '/api/get-all-regions').subscribe({
            next: (allRegions: Array<Region>) => {
                this._allRegions$.next(allRegions)
                console.log('Load régions')
            }, error: (e: Error) => {
                console.log(e)
            }
        })
    }

    getRole() {
        var user!: User

        this.userConnected$.subscribe(u => {
            if (u !== null) {
                user = u
            }
        })

        return user.roles
    }

}
