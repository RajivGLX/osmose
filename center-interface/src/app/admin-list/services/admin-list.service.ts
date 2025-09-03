import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { User } from '../../interface/user.interface';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { ToolsService } from '../../shared/services/tools.service';
import { environment } from '../../../environment/environment.development';

@Injectable({
    providedIn: 'root'
})
export class AdminListService {

    private _listAllAdmin$ = new BehaviorSubject<User[]>([])
        get listAllAdmin$() {
            return this._listAllAdmin$.asObservable()
        }

    public loaderAgGrid$ = new BehaviorSubject<boolean>(false)

    private listAllAdminLoaded = 0

    constructor(
        private http: HttpClient,
        private toolsService: ToolsService
    ) { }

    getAllAdmins(forceReload: boolean = false) {
        if (Date.now() - this.listAllAdminLoaded <= 1200000 && forceReload == false) {
            return
        }
        if(forceReload == false){
            this.loaderAgGrid$.next(true)
        }
        this.http.get<{ data: User[], message: string }>(environment.apiURL + '/api/get-all-admins').subscribe({
            next: (response: { data: User[], message: string }) => {
                this._listAllAdmin$.next(response.data)
                this.listAllAdminLoaded = Date.now()
                this.loaderAgGrid$.next(false)
            },
            error: (error: HttpErrorResponse) => {
                this.loaderAgGrid$.next(false)
                console.log(error)
                this.toolsService.openSnackBar(error.error, false);
            }
        })
    }

    updateUserStatus(user: User, newStatus: boolean) {
        console.log(user, newStatus)
        this.http.post<{ data: User, message: string }>(environment.apiURL + '/api/user-change-status', {id:user.id, valid:newStatus}).subscribe({
            next: (response : {message: string, data: User}) => {
                this.updateAdminInList(response.data)
                this.toolsService.openSnackBar(response.message, true)
            },
            error: (error: HttpErrorResponse) => {
                this.toolsService.openSnackBar(error.error.message, false)
            }
        });
    }

    updateAdminInList(updatedAdmin: User) {
        const currentAdmin = this._listAllAdmin$.value;
        const index = currentAdmin.findIndex(admin => admin.id === updatedAdmin.id);
        if (index !== -1) {
            currentAdmin[index] = updatedAdmin;
            this._listAllAdmin$.next([...currentAdmin]);
        }
    }

    addAdminToList(newAdmin: User) {
        const currentAdmin = this._listAllAdmin$.value;
        this._listAllAdmin$.next([...currentAdmin, newAdmin]);
    }
}
