import { Component } from '@angular/core';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { RegisterService } from '../services/register.service';

@Component({
    selector: 'app-verify-account',
    standalone: true,
    imports: [RouterModule],
    templateUrl: './verify-account.component.html',
    styleUrl: './verify-account.component.sass'
})
export class VerifyAccountComponent {
	token: string = "";

	constructor(private route: ActivatedRoute, private registerService: RegisterService) {}

	ngOnInit(): void {
		// Récupère le token d'activation du compte
		this.route.params.subscribe({
			next: (value: any) => {
				this.token = value.token;
			}
		});
	}

	// Renvoi un mail d'actiavtion
	sendNewMail() {
		this.registerService.resendMail(this.token);
	}
}
