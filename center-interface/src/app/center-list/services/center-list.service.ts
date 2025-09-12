import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { Center } from '../../interface/center.interface';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { environment } from '../../../environment/environment';
import { ToolsService } from '../../shared/services/tools.service';

@Injectable({
    providedIn: 'root'
})
export class CenterListService {

    private _listAllCenter$ = new BehaviorSubject<Center[]>([])
        get listAllCenter$() {
            return this._listAllCenter$.asObservable()
        }

    _allCenter$ = new BehaviorSubject<Center[]>([]);
        get allCenter$() {
            return this._allCenter$.asObservable();
        }    

    public loaderAgGrid$ = new BehaviorSubject<boolean>(false)

    private getListCentersReload = 0
    private getAllCenterReload = 0
    
    constructor(
        private http: HttpClient,
        private toolsService: ToolsService
    ) { }

    getListCenters(forceReload: boolean = false) {
        if (Date.now() - this.getListCentersReload <= 1200000 && !forceReload) {
            return
        }
        if(!forceReload){
            this.loaderAgGrid$.next(true)
        }
        this.http.get<{ data: Center[], message: string }>(environment.apiURL + '/api/get-list-center').subscribe({
            next: (response: { data: Center[], message: string }) => {
                console.log('Charge liste des centres')
                this._listAllCenter$.next(response.data)
                this.getListCentersReload = Date.now()
            },
            error: (error: HttpErrorResponse) => {
                this.loaderAgGrid$.next(false)
                this.toolsService.openSnackBar('error',error.error);
            }
        })
    }

    getAllCenters(forceReload: boolean = false): void {
        if (Date.now() - this.getAllCenterReload <= 1200000 && !forceReload) {
            return
        }
        this.http.get<{ data: Center[], message: string }>(environment.apiURL + '/api/get-list-center').subscribe({
            next: (response: { data: Center[], message: string }) => {
                console.log('Charge tous les centres pour select')
                this._allCenter$.next(response.data)
                this.getAllCenterReload = Date.now()
            },
            error: (error: HttpErrorResponse) => {
                this.toolsService.openSnackBar('error',error.error);
            }
        })
    }

    updateCenterStatus(center: Center, status: boolean) {
        this.http.post<{ data: Center, message: string }>(environment.apiURL + '/api/center-change-status', {id:center.id, active:status}).subscribe({
            next: (response : {message: string, data: Center}) => {
                this.updateCenterInList(response.data)
                this.toolsService.openSnackBar('success',response.message);
            },
            error: (error: HttpErrorResponse) => {
                this.updateCenterInList(center)
                this.toolsService.openSnackBar('error',error.error.message);
            }
        });
    }

    updateCenterInList(updatedCenter: Center) {
        const currentCenters = this._listAllCenter$.value;
        const index = currentCenters.findIndex(center => center.id === updatedCenter.id);
        if (index !== -1) {
            currentCenters[index] = updatedCenter;
            this._listAllCenter$.next([...currentCenters]);
        }
    }

    addCenterToList(newCenter: Center) {
        const currentCenters = this._listAllCenter$.value;
        this._listAllCenter$.next([...currentCenters, newCenter]);
    }

}
