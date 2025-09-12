import {HttpClient} from '@angular/common/http';
import {Injectable, signal, WritableSignal} from '@angular/core';
import { map, Observable} from 'rxjs';
import {environment} from '../../../environment/environment';
import {FormControl, FormGroup, Validators} from "@angular/forms";
import {Task} from "../../interface/task.interface";

@Injectable({
    providedIn: 'root'
})
export class OverviewService {
    taskForm = new FormGroup({
        description: new FormControl('', [Validators.required])
    });
    loadingInfoTask: WritableSignal<boolean> = signal(false);

    constructor(
        private http: HttpClient,
    ) {
    }

    getTasks(): Observable<Task[]> {
        return this.http.get<{ message: string, data: Task[] }>(environment.apiURL + '/api/get-all-task').pipe(
            map(response => response.data)
        );
    }

    createTask(description: string): Observable<{ message: string, data: Task }> {
        return this.http.post<{ message: string, data: Task }>(environment.apiURL + '/api/create-task', {
            description,
            checked: false
        })
    }

    updateTaskStatus(id: number, checked: boolean): Observable<any> {
        return this.http.post(environment.apiURL + '/api/task-update-status', {id, checked});
    }

}
