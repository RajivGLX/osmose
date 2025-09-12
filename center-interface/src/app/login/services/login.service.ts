import { HttpClient } from '@angular/common/http';
import {Injectable, signal, WritableSignal} from '@angular/core';
import { Router } from '@angular/router';
import { JwtHelperService } from '@auth0/angular-jwt';
import {BehaviorSubject, Subscription, timer} from 'rxjs';
import { environment } from '../../../environment/environment';
import { User } from '../../interface/user.interface';
import { ToolsService } from '../../shared/services/tools.service';
import { Region } from '../../interface/region.interface';
import {MatDialog} from "@angular/material/dialog";
import {ConfirmationDialogComponent} from "../../shared/confirmation-dialog/confirmation-dialog.component";

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

    private warningTimer?: Subscription;
    private readonly WARNING_TIME = 60000; // 60 secondes en millisecondes
    private tokenExpirationTimer?: Subscription;
    displayAttemptRemaining :WritableSignal<null | number> = signal(null)
    displayAccountBlocked : WritableSignal<boolean> = signal(false)

    constructor(
        private http: HttpClient,
        private jwtHelper: JwtHelperService,
        private router: Router,
        private toolsService: ToolsService,
        private dialog: MatDialog
    ) {
        this.checkExistingSession();
    }


    sendCredentials(credentials: any) {
        this._loading$.next(true)
        const headers = { 'Content-type': 'application/json' }
        const infosLog = { username: credentials.username, password: credentials.password }
        this.http.post(environment.apiURL + '/api/login_check', infosLog, { headers }).subscribe({
            next: (data: any) => {
                this._loading$.next(false)
                sessionStorage.setItem('token-auth', data.token);
                this._isLogged$.next(true)
                this.getConnectedUser()
                this._successMsg$.next('Connexion réalisée')
                this.router.navigateByUrl('/')
                // Démarrer le minuteur de déconnexion automatique
                this.startAutoLogoutTimer();
            },
            error: (err: any) => {
                if(err.error.nb_connection_attempt) {
                    this.displayAttemptRemaining.set(3 - err.error.nb_connection_attempt)
                    if(this.displayAttemptRemaining()! <= 0){
                        this.displayAccountBlocked.set(true)
                    }
                }
                this._loading$.next(false)
                switch (err.status) {
                    case 401:
                        this.toolsService.openSnackBar('error','Identifiants invalides')
                        break
                    case 403:
                        this.toolsService.openSnackBar('warning','vous n\'êtes pas autorisés à acceder à cette page')
                        break
                    case 500:
                        this.toolsService.openSnackBar('error','Une erreur serveur est survenue')
                        break
                    default:
                        this.toolsService.openSnackBar('error','Une erreur est survenue')
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
                if (value.roles.includes("ROLE_ADMIN_OSMOSE")) {
                    value.adminOsmose = true;
                    this._userConnected$.next(value);
                }else{
                    value.adminOsmose = false;
                    this._userConnected$.next(value);
                }
                console.log(this._userConnected$.value)

            }, error: (e: Error) => {
                this.toolsService.openSnackBar('warning','Session expirée. Veuillez vous reconnecter.');
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


    private startAutoLogoutTimer(): void {
        const token = sessionStorage.getItem('token-auth');
        if (!token) return;

        const expirationTime = this.jwtHelper.getTokenExpirationDate(token);
        if (!expirationTime) return;

        const timeUntilExpiration = expirationTime.getTime() - Date.now();

        // Si le token est déjà expiré, déconnexion immédiate
        if (timeUntilExpiration <= 0) {
            this.logout();
            return;
        }

        // Si le temps restant est inférieur à 60 secondes, afficher immédiatement la popup
        if (timeUntilExpiration <= this.WARNING_TIME) {
            this.showTokenExpirationWarning();
            return;
        }

        // Minuteur pour afficher l'avertissement 60 secondes avant expiration
        const timeUntilWarning = timeUntilExpiration - this.WARNING_TIME;
        this.warningTimer = timer(timeUntilWarning).subscribe(() => {
            this.showTokenExpirationWarning();
        });
    }

    private showTokenExpirationWarning(): void {
        const dialogRef = this.dialog.open(
            ConfirmationDialogComponent, {
                data: {
                    title: 'Session sur le point d\'expirer.',
                    message: 'Votre session va expirer dans moins d\'une minute.',
                    underMessage: 'Souhaitez-vous rester connecté ?',
                    btnOkText: 'Rester connecté',
                    btnCancelText: 'Se déconnecter',
                    showCountdown: true,
                    countdown: 60
                },
                width: '35%',
                panelClass: 'confirm-dialog',
                disableClose: true
            });

        dialogRef.afterClosed().subscribe(result => {
            if (result) {
                this.refreshToken();
            } else {
                this.logout();
            }
        });

        // Minuteur final pour déconnexion automatique après 60 secondes
        this.tokenExpirationTimer = timer(this.WARNING_TIME).subscribe(() => {
            dialogRef.close();
            this.toolsService.openSnackBar('warning','Session expirée. Déconnexion automatique.');
            this.logout();
        });
    }

    private refreshToken(): void {
        this.http.post(environment.apiURL + '/api/token/refresh', {}).subscribe({
            next: (data: any) => {
                sessionStorage.setItem('token-auth', data.token);
                this.clearAutoLogoutTimer();
                this.startAutoLogoutTimer();
                this.toolsService.openSnackBar('success','Session prolongée avec succès.');
            },
            error: (err: any) => {
                this.toolsService.openSnackBar('error','Impossible de prolonger la session. Déconnexion.');
                this.logout();
            }
        });
    }

    /**
     * Arrête le minuteur de déconnexion automatique.
     */
    private clearAutoLogoutTimer(): void {
        if (this.tokenExpirationTimer) {
            this.tokenExpirationTimer.unsubscribe();
            this.tokenExpirationTimer = undefined;
        }
        if (this.warningTimer) {
            this.warningTimer.unsubscribe();
            this.warningTimer = undefined;
        }
    }

    /**
     * Vérifie s'il existe une session active au démarrage de l'application
     * et démarre le minuteur d'expiration si nécessaire.
     */
    private checkExistingSession(): void {
        const token = sessionStorage.getItem('token-auth');

        if (token && !this.jwtHelper.isTokenExpired(token)) {
            // L'utilisateur est déjà connecté
            this._isLogged$.next(true);

            // Démarrer le minuteur de déconnexion automatique
            this.startAutoLogoutTimer();
        } else if (token) {
            // Le token existe mais est expiré, nettoyer la session
            this.logout();
        }
    }

}
