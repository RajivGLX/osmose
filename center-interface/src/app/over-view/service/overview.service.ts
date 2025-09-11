import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { environment } from '../../../environment/environment';

@Injectable({
    providedIn: 'root'
})
export class OverviewService {

    constructor(private http: HttpClient) { }

}
