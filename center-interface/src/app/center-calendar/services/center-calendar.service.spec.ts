import { TestBed } from '@angular/core/testing';

import { CenterCalendarServices } from './center-calendar.service';

describe('CenterCalendarService', () => {
  let service: CenterCalendarServices;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(CenterCalendarServices);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
