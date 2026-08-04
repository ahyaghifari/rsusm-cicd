---
name: Empathetic Healthcare
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#52424d'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#84727e'
  outline-variant: '#d6c0ce'
  surface-tint: '#9b3191'
  primary: '#982e8e'
  on-primary: '#ffffff'
  primary-container: '#b649a9'
  on-primary-container: '#fffbff'
  inverse-primary: '#ffabee'
  secondary: '#006c4b'
  on-secondary: '#ffffff'
  secondary-container: '#96f6c8'
  on-secondary-container: '#00734f'
  tertiary: '#4d51b2'
  on-tertiary: '#ffffff'
  tertiary-container: '#666acc'
  on-tertiary-container: '#fffbff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffd7f3'
  primary-fixed-dim: '#ffabee'
  on-primary-fixed: '#390036'
  on-primary-fixed-variant: '#7e1277'
  secondary-fixed: '#96f6c8'
  secondary-fixed-dim: '#7ad9ad'
  on-secondary-fixed: '#002114'
  on-secondary-fixed-variant: '#005137'
  tertiary-fixed: '#e1e0ff'
  tertiary-fixed-dim: '#c0c1ff'
  on-tertiary-fixed: '#04006d'
  on-tertiary-fixed-variant: '#373a9b'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  headline-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.25'
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.05em
  headline-xl-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: '1.2'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 8px
  container-max: 1280px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 40px
  section-gap: 80px
---

## Brand & Style

This design system is built upon the principle of **Empathetic Professionalism**. It balances the clinical precision required of a medical institution with the warmth and approachability needed by patients and their families. The target audience spans from tech-savvy young adults to seniors requiring high legibility and intuitive navigation.

The design style is **Corporate / Modern** with a focus on high-quality CMS-inspired structures. It prioritizes clarity, generous whitespace, and a "human" touch through soft geometry and intentional information hierarchy. The interface should feel organized and authoritative, yet never cold or intimidating, ensuring users feel supported during their healthcare journey.

## Colors

The palette is anchored by a vibrant Magenta (#c758b9) used for primary actions and brand recognition, representing vitality and care. This is tempered by a Deep Green (#057a55) for secondary elements, success states, and health-positive indicators. 

- **Primary:** Used for the main CTA buttons, active navigation states, and key brand highlights.
- **Secondary:** Used for health-related badges, success messaging, and secondary UI elements to provide a calming, grounded counterpoint.
- **Neutral:** A range of slate greys is used for text and iconography to ensure high contrast and professional sobriety.
- **Backgrounds:** Utilize extremely soft tints of the secondary color or neutral greys to define content areas without introducing visual noise.

## Typography

This design system utilizes **Plus Jakarta Sans** across all levels to maintain a cohesive, friendly, and modern appearance. The font's geometric yet soft curves ensure excellent readability for elderly users while maintaining a contemporary aesthetic.

- **Scale:** A generous typographic scale is used to create clear entry points into content.
- **Readability:** Body text is set at a minimum of 16px to ensure accessibility. Line heights are kept generous (1.6) to prevent "text-crowding," which is essential for users under stress or with visual impairments.
- **Hierarchy:** Use the semi-bold and bold weights to clearly distinguish section headers from supporting information.

## Layout & Spacing

The layout follows a **Fixed Grid** model on desktop, centering content within a 1280px container to prevent excessive eye-strain on wide monitors.

- **Grid:** A 12-column grid is used for desktop, collapsing to 4 columns for mobile. 
- **Rhythm:** An 8px linear spacing scale governs all margins and paddings, ensuring mathematical harmony across components.
- **Whitespace:** Emphasize "Generous Whitespace." Section gaps should be significant (80px+) to allow the user's eyes to rest and to clearly separate different healthcare services or informational modules.
- **Mobile Adaptivity:** On mobile, margins reduce to 16px, and complex 3-column card layouts must reflow into a single-column vertical stack.

## Elevation & Depth

To maintain a professional and clean CMS-like feel, the design system uses **Tonal Layers** combined with **Ambient Shadows**.

- **Surfaces:** The base background is slightly off-white (#F8FAFC). Primary content areas use pure white surfaces to create a natural "lift."
- **Shadows:** Use extremely soft, diffused shadows with a large blur radius and low opacity (e.g., `0 10px 25px -5px rgba(0,0,0,0.05)`). This creates a sense of depth without looking "gamey" or overly digital.
- **Interaction:** On hover, cards should subtly lift (increase shadow spread) and provide a slight vertical translation (-4px) to signal interactivity.

## Shapes

The shape language is defined as **Rounded**, utilizing a standard 0.5rem (8px) corner radius for most components. This choice avoids the clinical harshness of sharp corners while remaining more professional than fully pill-shaped "playful" designs.

- **Buttons & Inputs:** Follow the 8px standard.
- **Large Cards:** Utilize `rounded-lg` (16px) to frame content softly.
- **Icons:** Use icons with rounded terminals to match the typeface and shape language.

## Components

### Cards
Cards are the primary container for doctor profiles, department links, and news items. They feature a white background, a soft ambient shadow, and 16px of roundedness. Internal padding should be a minimum of 24px.

### Search & Filters
Search bars should be prominent, featuring a "Search doctors or services..." placeholder. Filters use a "Clean Pill" style—secondary color outlines that fill in when selected. This ensures the user can easily narrow down complex medical lists.

### Buttons
- **Primary:** Solid Magenta (#c758b9) with white text. High contrast, high visibility.
- **Secondary:** Outlined Deep Green (#057a55) or subtle grey ghost buttons for less critical actions.

### Input Fields
Inputs are structured with a light grey border (1px) that turns primary magenta upon focus. Labels are always visible above the field (never hidden as placeholders) to assist users with cognitive challenges.

### Footer
A "Clean Footer" style: Large, structured blocks of links on a very light neutral-tinted background. It must include clear contact information, a hospital location map link, and accessibility toggles.

### Lists & Tables
Used for appointment history or lab results. Use zebra-striping with a very faint tint of the secondary color to keep rows readable.