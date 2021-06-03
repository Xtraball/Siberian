import { AdUnitIDOption, IBannerRequest } from '@admob-plus/core';
import { AdBase, TestIds } from './base';
import { MobileAd } from './base';
import type { MobileAdOptions } from './base';
export declare class BannerAd extends MobileAd {
    constructor({ adUnitId }: MobileAdOptions);
    show(opts: IBannerRequest): Promise<unknown>;
    hide(): Promise<unknown>;
}
export default class Banner extends AdBase {
    protected testIdForAndroid: TestIds;
    protected testIdForIOS: TestIds;
    show(opts: IBannerRequest): Promise<unknown>;
    hide(id: AdUnitIDOption): Promise<unknown>;
}
