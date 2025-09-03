import { TestBed } from '@angular/core/testing';

import { BookingViewService } from './booking-view.service';

describe('BookingViewService', () => {
  let service: BookingViewService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(BookingViewService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
