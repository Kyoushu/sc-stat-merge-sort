import "../../scss/app.scss";

import React from 'react';
import PropTypes from 'prop-types';

export default class App extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div>
                App goes here
            </div>
        )
    }

}

App.propTypes = {};

App.defaultProps = {};