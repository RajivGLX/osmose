import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'sortDays',
    standalone: true
})
export class SortDaysPipe implements PipeTransform {
    transform(days: { [key: string]: any }): { key: string, value: any }[] {
        if (!days) return [];
        
        // Convertir l'objet en tableau de paires clé-valeur
        const entries = Object.entries(days).map(([key, value]) => ({ key, value }));
        
        // Trier les entrées par date (format attendu: YYYY/MM/DD)
        return entries.sort((a, b) => {
            const dateA = new Date(a.key.replace(/(\d+)\/(\d+)\/(\d+)/, '$1-$2-$3'));
            const dateB = new Date(b.key.replace(/(\d+)\/(\d+)\/(\d+)/, '$1-$2-$3'));
            return dateA.getTime() - dateB.getTime();
        });
    }
}