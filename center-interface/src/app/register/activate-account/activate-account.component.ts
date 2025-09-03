import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { RegisterService } from '../services/register.service';

@Component({
    selector: 'app-activate-account',
    standalone: true,
    imports: [RouterModule],
    templateUrl: './activate-account.component.html',
    styleUrl: './activate-account.component.sass'
})
export class ActivateAccountComponent implements OnInit {
	token: string = "";
	message: string = "";

	constructor(private route: ActivatedRoute, private registerService: RegisterService) {}

	ngOnInit(): void {
		// Récupère le token d'activation
		this.route.params.subscribe({
			next: (value: any) => {
				this.token = value.token;
			}
		});

		// Active le compte
		this.registerService.activateAccount(this.token).subscribe({
			next: (v: any) => {
				this.message = v.message;
			},
			error: (e: any) => {
				this.message = e.error.message;
			}
		});
	}
}
