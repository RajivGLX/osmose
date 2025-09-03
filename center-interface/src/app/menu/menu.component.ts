import { CommonModule } from '@angular/common';
import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { MatExpansionModule } from '@angular/material/expansion';
import { Router, RouterLink, RouterLinkActive } from '@angular/router';
import { LoginService } from '../login/services/login.service';
import { User } from '../interface/user.interface';
import { NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { Observable } from 'rxjs';

@Component({
    selector: 'app-menu',
    standalone: true,
    imports: [
        CommonModule, 
        RouterLink, 
        RouterLinkActive, 
        MatExpansionModule, 
    ],
    templateUrl: './menu.component.html',
    styleUrl: './menu.component.sass'
})
export class MenuComponent implements OnInit {


    @Output() toggleSideNav = new EventEmitter<boolean>(false)
    currentUser: Observable<User | null> = this.loginService.userConnected$
    isToggled: boolean = false
    nombreNotificationsNonLues!: number

    connectNotifications: boolean = true

    constructor(
        private loginService: LoginService,
        private router: Router,
    ) { }

    ngOnInit(): void {

        // Écouter les événements de navigation pour faire défiler la page vers le haut
        this.router.events.pipe(
            filter(event => event instanceof NavigationEnd)
        ).subscribe(() => {
            window.scrollTo(0, 0);
        });
    }

    // Déconnecte l'utilisateur
    logout() {
        this.loginService.logout()
    }

    // Réduit ou étend la sidebar
    toggle() {
        this.isToggled = !this.isToggled
        this.toggleSideNav.emit(this.isToggled)
    }
    
}
