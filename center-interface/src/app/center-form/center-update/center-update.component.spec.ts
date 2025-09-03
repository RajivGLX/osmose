import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CenterUpdateComponent } from './center-update.component';

describe('SlotsComponent', () => {
  let component: CenterUpdateComponent;
  let fixture: ComponentFixture<CenterUpdateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CenterUpdateComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(CenterUpdateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
