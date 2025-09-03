import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { LoginService } from './login/services/login.service';
import { MainComponent } from './main/main.component';
import { SidebarComponent } from './sidebar/sidebar.component';
import { HttpClientModule } from '@angular/common/http';


@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    CommonModule,
    RouterOutlet,
    RouterLink,
    RouterLinkActive,
    MainComponent,
    SidebarComponent,
    HttpClientModule
  ],

  templateUrl: './app.component.html',
  styleUrl: './app.component.sass'
})
export class AppComponent {

  toggled: boolean = false

  constructor(private loginService: LoginService) { }

  isLogged = this.loginService.isLogged$
  title = 'nota-risques';

  /**
   * Permet de réduire ou d'étendre la sidebar
   *
   * @param $event - booléen qui indique si la sidebar est réduire (true) ou étendue (false)
   */
  toggleSideBar($event: boolean) {
    this.toggled = $event
  }
}