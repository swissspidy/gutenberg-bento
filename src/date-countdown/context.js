import { createContext, useContext, useMemo } from '@wordpress/element';

export const Context = createContext({});

export function CountdownContextProvider({ value, children }) {
	const context = useContext(Context);
	const nextValue = useMemo(
		() => ({ ...context, ...value }),
		[context, value]
	);

	return <Context.Provider value={nextValue} children={children} />;
}
