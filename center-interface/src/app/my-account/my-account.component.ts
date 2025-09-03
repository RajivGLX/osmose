import { AsyncPipe, CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { ActivatedRoute } from '@angular/router';
import { Observable } from 'rxjs';
import { LoaderComponent } from "../loader/loader.component";
import { User } from '../interface/user.interface';
import { LoginService } from '../login/services/login.service';
import { UpdateProfilComponent } from './update-profil/update-profil.component';
import { RolePipe } from '../utils/pipe/role.pipe';
import { GestionUserComponent } from './gestion-user/gestion-user.component';

@Component({
    selector: 'app-my-account',
    standalone: true,
    imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    AsyncPipe,
    MatProgressSpinnerModule,
    MatIconModule,
    LoaderComponent,
    UpdateProfilComponent,
    RolePipe,
    GestionUserComponent
],
    templateUrl: './my-account.component.html',
    styleUrl: './my-account.component.sass'
})
export class MyAccountComponent implements OnInit {

    currentUser: Observable<User | null> = this.loginService.userConnected$
    ongletToDisplay: number = 1

    constructor(
        private loginService: LoginService, 
        private activatedRoute: ActivatedRoute
    ) { }

    ngOnInit(): void {
        
        // Vérifie l'onglet à afficher
        this.activatedRoute.snapshot.queryParams['onglet'] == 'preferences' ? this.ongletToDisplay = 5 : ''

        this.currentUser.subscribe(user => {
            if (user !== null) {
                
            }
        })
    }

    // Change l'onglet à afficher
    switchOnglet(onglet: number) {
        this.ongletToDisplay = onglet
    }

    checkRoles(roles: string[]): boolean {
        var access = false
        this.currentUser.subscribe({
            next: (u) => {
                if (u !== null) {
                    if (u.roles.some((r: string) => roles.includes(r))) {
                        access = true
                    } else {
                        access = false
                    }
                } else {
                    access = false
                }
            }
        })

        return access
    }
}
