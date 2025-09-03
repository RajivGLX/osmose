import { TestBed } from '@angular/core/testing';

import { AdminListService } from './admin-list.service';

describe('AdminListService', () => {
  let service: AdminListService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AdminListService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
