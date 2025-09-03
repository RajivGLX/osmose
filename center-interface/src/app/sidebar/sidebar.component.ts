import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { LoginService } from '../login/services/login.service';
import { MenuComponent } from '../menu/menu.component';

@Component({
    selector: 'app-sidebar',
    standalone: true,
    imports: [MenuComponent],
    templateUrl: './sidebar.component.html',
    styleUrl: './sidebar.component.sass'
})
export class SidebarComponent implements OnInit {

    constructor(private loginService: LoginService) { }

    @Output() toggleEmit = new EventEmitter<boolean>(false)

    ngOnInit(): void {
        // Récupère l'utilisateur connecté
        this.loginService.getConnectedUser()
        // this.loginService.userConnected$.subscribe(user => console.log(user))
    }

    /**
     * Réduit ou étend la sidebar
     * 
     * @param $event - Booléen correspondant à l'état de la sidebar 
     */
    toggleSideBar($event: boolean) {
        this.toggleEmit.emit($event)
    }
}
