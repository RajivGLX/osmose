import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {User} from '../../../interface/user.interface';
import {BehaviorSubject} from 'rxjs';
import {environment} from '../../../../environment/environment';

@Injectable({
    providedIn: 'root'
})
export class GestionUserService {

    constructor(private http: HttpClient) {
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
        if (Date.now() - this.listeUsersEtudeLoaded <= 3600000 && !forceReload) {
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

    
	addAdminToList(newAdmin: User) {
        const currentAdmin = this._listeAdminCenter$.value;
        this._listeAdminCenter$.next([...currentAdmin, newAdmin]);
    }

}
