import { MobileAd } from './shared';
export default class InterstitialAd extends MobileAd {
    isLoaded(): Promise<boolean>;
    load(): Promise<void>;
    show(): Promise<void>;
}
