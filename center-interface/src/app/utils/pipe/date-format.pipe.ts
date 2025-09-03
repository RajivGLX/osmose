import { Injectable, Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'formatageDate',
    standalone: true
})
@Injectable({
    providedIn: 'root'  // Pour permettre l'injection
})
export class FormatageDatePipe implements PipeTransform {
    transform(dateString: Date): string {
        const date = new Date(dateString);
        const options: Intl.DateTimeFormatOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        };
        return new Intl.DateTimeFormat('fr-FR', options).format(date);
    }
}