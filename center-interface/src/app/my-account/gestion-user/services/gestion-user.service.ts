import {HttpClient, HttpErrorResponse} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {User} from '../../../interface/user.interface';
import {BehaviorSubject, Observable} from 'rxjs';
import {environment} from '../../../../environment/environment';
import {JsonResponseInterface} from '../../../shared/interfaces/json-response-interface';
import { ToolsService } from '../../../shared/services/tools.service';

@Injectable({
    providedIn: 'root'
})
export class GestionUserService {

    constructor(
        private http: HttpClient, 
        private toolsService: ToolsService,) {
    }

    private _listeAdminCenter$ = new BehaviorSubject<User[]>([]);

    private _loading$ = new BehaviorSubject<boolean>(false);
    get loading$() {
        return this._loading$.asObservable();
    }

    private _statusLoading$ = new BehaviorSubject<boolean>(false);
    get statusLoading$() {
        return this._statusLoading$.asObservable();
    }

    get listUsersEtude$() {
        return this._listeAdminCenter$.asObservable();
    }

    private listeUsersEtudeLoaded = 0;


    getListeUsersCenter(forceReload: boolean = false) {
        // requete par heure
        this._loading$.next(true)
        if (Date.now() - this.listeUsersEtudeLoaded <= 3600000 && forceReload == false) {
            this._loading$.next(false)
            return;
        }

        this.http.get<User[]>(environment.apiURL + '/api/get-liste-users').subscribe({
            next: (data: User[]) => {
                this._loading$.next(false)
                this._listeAdminCenter$.next(data);
                this.listeUsersEtudeLoaded = Date.now();

            },
            error: (err: Error) => {
                this._loading$.next(false)
                console.log('erreur ' + err);

            }
        });
    }


    updateUserStatus(userId: number, accountStatus: boolean) {
        this._statusLoading$.next(true)
        this.http.post<JsonResponseInterface>(environment.apiURL + '/api/userDesactivatedByAdmin', {
            id: userId,
            accountStatus
        }).subscribe({
            next: (data: JsonResponseInterface) => {
                this._statusLoading$.next(false)
                this.toolsService.openSnackBar(data.message, data.success);
                this.getListeUsersCenter(true);
            },
            error: (err: HttpErrorResponse) => {
                this._statusLoading$.next(false)
                this.toolsService.openSnackBar(err.error.message, false);

            }
        });
    }

    
	addAdminToList(newAdmin: User) {
        const currentAdmin = this._listeAdminCenter$.value;
        this._listeAdminCenter$.next([...currentAdmin, newAdmin]);
    }

}
