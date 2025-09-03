import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PopupUpdateUserComponent } from './popup-update-user.component';

describe('PopupUpdateUserComponent', () => {
  let component: PopupUpdateUserComponent;
  let fixture: ComponentFixture<PopupUpdateUserComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PopupUpdateUserComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(PopupUpdateUserComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
