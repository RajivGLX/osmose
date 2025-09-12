import { Injectable } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';

@Injectable({
  providedIn: 'root'
})
export class ToolsService {

    constructor(private _snackBar: MatSnackBar) { }

    openSnackBar(context: 'success' | 'error' | 'info' | 'warning', message: string) {
        this._snackBar.open(message, 'fermer', {
            duration: 4000,
            verticalPosition: 'top',
            horizontalPosition: 'center',
            panelClass: context
        })
    }

}
