import { SlotPipe } from './slot.pipe';

describe('SafePipe', () => {
    it('create an instance', () => {
        const pipe = new SlotPipe();
        expect(pipe).toBeTruthy();
    });
});