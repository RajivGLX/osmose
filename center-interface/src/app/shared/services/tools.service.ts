import { Injectable } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';

@Injectable({
  providedIn: 'root'
})
export class ToolsService {

  constructor(private _snackBar: MatSnackBar) { }


    /**
     * Affiche un message à l'utilisateur
     *
     * @param msg - Message affiché
     * @param type - Type de message (success ou error)
     */
  openSnackBar(msg: string, type: boolean) {
    this._snackBar.open(msg, 'fermer', {
      horizontalPosition: 'center',
      verticalPosition: 'top',
      duration: 10000,
      panelClass: [!type ? 'error' : 'success', 'snackbar-center']
    });
  }

}
