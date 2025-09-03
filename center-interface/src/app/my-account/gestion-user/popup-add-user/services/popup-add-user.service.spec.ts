import { TestBed } from '@angular/core/testing';

import { PopupAddUserService } from './popup-add-user.service';

describe('PopupAddUserService', () => {
  let service: PopupAddUserService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PopupAddUserService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
