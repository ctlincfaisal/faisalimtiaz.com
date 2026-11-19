import { DecoratorNode } from 'lexical';

export class MarketingImageNode extends DecoratorNode {
    static getType() {
        return 'marketing-image';
    }

    static clone(node) {
        return new MarketingImageNode(node.__src, node.__alt, node.__width, node.__key);
    }

    static importJSON(serializedNode) {
        return new MarketingImageNode(serializedNode.src, serializedNode.alt, serializedNode.width || '');
    }

    static importDOM() {
        return {
            img: () => ({
                conversion: (element) => {
                    const src = element.getAttribute('src');
                    if (!src) return null;

                    return {
                        node: new MarketingImageNode(
                            src,
                            element.getAttribute('alt') || '',
                            element.getAttribute('width') || '',
                        ),
                    };
                },
                priority: 1,
            }),
        };
    }

    constructor(src, altText = '', width = '', key) {
        super(key);
        this.__src = src;
        this.__alt = altText;
        this.__width = width;
    }

    exportJSON() {
        return {
            type: 'marketing-image',
            version: 1,
            src: this.__src,
            alt: this.__alt,
            width: this.__width,
        };
    }

    exportDOM() {
        const element = document.createElement('img');
        element.src = this.__src;
        element.alt = this.__alt;
        if (this.__width) element.setAttribute('width', this.__width);
        element.style.maxWidth = '100%';
        element.style.height = 'auto';

        return { element };
    }

    createDOM() {
        const element = document.createElement('img');
        element.src = this.__src;
        element.alt = this.__alt;
        if (this.__width) element.setAttribute('width', this.__width);
        element.style.maxWidth = '100%';
        element.style.height = 'auto';
        element.setAttribute('data-marketing-image', 'true');

        return element;
    }

    updateDOM(previousNode, dom) {
        if (previousNode.__src !== this.__src) dom.src = this.__src;
        if (previousNode.__alt !== this.__alt) dom.alt = this.__alt;
        if (previousNode.__width !== this.__width) {
            if (this.__width) dom.setAttribute('width', this.__width);
            else dom.removeAttribute('width');
        }

        return false;
    }

    getTextContent() {
        return this.__alt ? `[Image: ${this.__alt}]` : '[Image]';
    }

    isInline() {
        return false;
    }

    isKeyboardSelectable() {
        return true;
    }

    setWidth(width) {
        const writable = this.getWritable();
        writable.__width = width || '';
    }

    getWidth() {
        return this.getLatest().__width;
    }
}

export function $createMarketingImageNode({ src, altText = '', width = '' }) {
    return new MarketingImageNode(src, altText, width);
}

export function $isMarketingImageNode(node) {
    return node instanceof MarketingImageNode;
}
