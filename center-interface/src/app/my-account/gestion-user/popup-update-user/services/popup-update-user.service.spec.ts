import { TestBed } from '@angular/core/testing';

import { PopupEditUtilisateurService } from './popup-edit-utilisateur.service';

describe('PopupEditUtilisateurService', () => {
  let service: PopupEditUtilisateurService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PopupEditUtilisateurService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
