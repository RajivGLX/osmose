import { Injectable } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from "@angular/forms";
import { User } from "../../../../interface/user.interface";
import { Observable } from "rxjs";
import { JsonResponseInterface } from "../../../../shared/interfaces/json-response-interface";
import { environment } from "../../../../../environment/environment";
import { HttpClient } from "@angular/common/http";

@Injectable({
    providedIn: 'root'
})
export class PopupUpdateUserService {
    updateUserForm!: FormGroup;
    listeRoles = ['ROLE_ADMIN', 'ROLE_COMPTABLE', 'ROLE_NOTAIRE'];

    constructor(private fb: FormBuilder, private http: HttpClient) {
    }
    getListeRoles(): Array<string> {
        return this.listeRoles
    }

    // update FORM //
    updateUserFormInitialise(user: User) {
        this.updateUserForm = this.fb.group({
            id: [user.id, Validators.required],
            nom: [user.lastname, [Validators.required, Validators.minLength(2)]],
            prenom: [user.firstname, [Validators.required, Validators.minLength(2)]],
            email: [user.email, [Validators.required, Validators.email]],
            roles: this.fb.array(this.initializeRolesForm(user.roles), Validators.required)
        })
        return this.updateUserForm;
    }

    // INITIALISATION DU FORM ROLES
    initializeRolesForm(roles: string[]): FormControl[] {
        return roles.map(role => this.fb.control(role));
    }

    onAddingRole(role: string): void {
        const valArray: FormArray = this.updateUserForm.get('roles') as FormArray;
        valArray.clear();
        valArray.push(this.fb.control('ROLE_USER'));
        valArray.push(this.fb.control(role));

    }

    updateUserInfos(): Observable<JsonResponseInterface> {
        return this.http.post<JsonResponseInterface>(environment.apiURL + '/api/updateUserInfos', this.updateUserForm.value);
    }
}
